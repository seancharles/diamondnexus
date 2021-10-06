<?php

ini_set("display_errors", false);

# load magento classes from vendor
require '/var/www/magento/app/bootstrap.php';

class CartFeed
{
    private $db;
    private $stores;
    private $metalOptions;
    private $attributeCodeMap;
    private $storeId;
    private $quoteId;
    private $quote;
    private $quoteItems;
    private $graphqlEndpoint;
    private $guzzleClient;

    private $shapeMap = [
        2842 => 'round',
        2847 => 'oval',
        2850 => 'pear',
        2848 => 'emerald',
        2845 => 'cushion',
        2843 => 'princess',
        2849 => 'radiant',
        2844 => 'asscher',
        2846 => 'heart',
        2851 => 'marquise'
    ];

    function __construct($quoteId) {
        # get env variables for host and elastic
        $this->env  = include('../../../app/etc/env.php');

        # initiate configurations
        $this->db = $this->getPdoConnection();

        $this->stores = $this->getStoreConfig();

        # get metal option configs from db
        $this->getMetalOptions();

        $this->guzzleClient = new \GuzzleHttp\Client(['verify' => false]);

        return $this->getQuote($quoteId);
    }

    function getStoreConfig() {
        return [
            1 => [
                'host' => 'https://www.diamondnexus.com/',
                'cdn' => 'https://assets.diamondnexus.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
            ],
            12 => [
                'host' => 'https://www.1215diamonds.com/',
                'cdn' => 'https://assets.1215diamonds.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
            ],
            11 => [
                'host' => 'https://www.foreverartisans.com/',
                'cdn' => 'https://assets.foreverartisans.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
            ]
        ];
    }

    function getPdoConnection() {
        $dbName = $this->env['db']['connection']['default']['dbname'];
        $dbHost = $this->env['db']['connection']['default']['host'];
        $dbUser = $this->env['db']['connection']['default']['username'];
        $dbPass = $this->env['db']['connection']['default']['password'];

        return new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    }

    function getQuote($quoteId = 0) {
        // set the quote id
        $this->quoteId = $quoteId;

        // get the quote
        $this->quote = $this->db->query(" SELECT * FROM quote WHERE entity_id = {$this->quoteId};")->fetch(PDO::FETCH_ASSOC);

        // set store id
        $this->storeId = $this->quote['store_id'];

        # must have trailing slash or you will get 401 errors
        $this->graphqlEndpoint = $this->stores[$this->storeId]['host'] . "graphql/";

        $this->getQuoteItems();
    }

    function getHost() {
        return $this->stores[$this->storeId]['host'];
    }

    function getCDN() {
        return $this->stores[$this->storeId]['cdn'];
    }

    function getMetalFromBuyRequest($buyRequest = null) {
        if( isset($this->metalOptions[$buyRequest['super_attribute'][145]]) === true ) {
            return $this->metalOptions[ $buyRequest['super_attribute'][145] ];
        } else {
            return false;
        }
    }

    function getBuyRequest($product) {
        if( isset($product['buy_request']) === true ) {
            return unserialize($product['buy_request']);
        } else {
            return false;
        }
    }

    function getMetalOptions() {
        $metalQuery = "SELECT
								o.option_id,
								value
							FROM
								eav_attribute_option o
							INNER JOIN
								eav_attribute_option_value v ON o.option_id = v.option_id
							WHERE
								attribute_id = 145;";

        $result = $this->db->query($metalQuery)->fetchAll();

        if (sizeof($result)  > 0) {

            $temp = array();

            foreach ($result as $metalType) {
                $temp[$metalType['option_id']] = strtolower($metalType['value']);
            }

            $this->metalOptions = $temp;
        }
    }

    function getAttributeCodeMap()
    {
        $attributeQuery = "SELECT attribute_id, attribute_code FROM eav_attribute;";
        $attributeList = $this->db->query($attributeQuery)->fetchAll(PDO::FETCH_OBJ);
        foreach ($attributeList as $attribute) {
            $this->attributeCodeMap[$attribute->attribute_id] = $attribute->attribute_code;
        }
    }

    function getQuoteItems()
    {
        $products = [];

        $itemsQuery = "SELECT
                                p.entity_id,
                                p.sku,
                                p.type_id,
                                p.attribute_set_id,
                                c.product_id child_product_id,
                                c.sku child_sku,
                                o.value as buy_request
							FROM
								quote_item i
							INNER JOIN
								catalog_product_entity p ON i.product_id = p.entity_id
                            LEFT JOIN
                                quote_item c ON i.item_id = c.parent_item_id
                            LEFT JOIN
                                quote_item_option o ON i.item_id = o.item_id AND o.code = 'info_buyRequest'
							WHERE
							    i.parent_item_id IS NULL
						    AND
								i.quote_id = {$this->quoteId}
                            GROUP BY
                                p.entity_id;";

        $this->quoteItems = $this->db->query($itemsQuery)->fetchAll(PDO::FETCH_OBJ);

        foreach ($this->quoteItems as $item) {

            # get product info from graphql
            if ($item->attribute_set_id == 31) {
                # tf stones use a different graph endpoint
                $product = $this->getStoneProductBySku($item->sku);
            } else {
                if ($item->type_id == 'configurable') {
                    $product = $this->getConfigurableProductBySku($item->sku);
                } else {
                    $product = $this->getSimpleProductBySku($item->sku);
                }
            }

            $buyRequest = json_decode($item->buy_request);

            if (isset($buyRequest->super_attribute) === true) {
                $configOptions = (array) $buyRequest->super_attribute;
            }

            # get image gallery
            $imageGallery = $product->media_gallery;

            $images = [];
            $regularPrice = 0;
            $finalPrice = 0;

            if ($item->type_id == 'configurable') {
                # get price from variations
                foreach ($product->variants as $variant) {
                    if ($item->child_product_id == $variant->product->id) {
                        $regularPrice = $variant->product->price_range->minimum_price->regular_price->value;

                        # special pricing overrides catalog price rules
                        if ($variant->product->special_price != null) {
                            $finalPrice = $variant->product->special_price;
                        } else {
                            $finalPrice = $variant->product->price_range->minimum_price->final_price->value;
                        }
                        break;
                    }
                }
            } else {
                # pull price for simple
                $regularPrice = $product->price_range->minimum_price->regular_price->value;

                # special pricing overrides catalog price rules
                if ($product->special_price != null) {
                    $finalPrice = $product->special_price;
                } else {
                    $finalPrice = $product->price_range->minimum_price->final_price->value;
                }
            }

            if (isset($configOptions[145]) === true) {
                foreach ($imageGallery as $image) {
                    $label = strtolower($image->label);

                    if (strlen($label) > 0) {
                        if (strpos($label, "default") !== false) {
                            $metalType = $this->metalOptions[$configOptions[145]];

                            if (strpos($label, $metalType) !== false) {
                                $images[] = $image->image_path;
                            }
                        }
                    }
                }
            }

            $tempProduct = [
              'id' => $item->entity_id,
              'name' => $product->name,
              'price' => $regularPrice,
              'special_price' => $finalPrice
            ];

            if ($item->attribute_set_id == 31) {

                if (count($images) == 0) {
                    $images[] = $product->media_gallery[0]->image_path;
                }

                $tempProduct['img'] = $images[0];
                $tempProduct['url'] = $this->stores[$this->storeId]['host'] . $product->url_key;
            } else {

                if (count($images) == 0) {
                    $images[] = $this->stores[$this->storeId]['cdn'] . $product->media_gallery[0]->image_path;
                }

                $tempProduct['img'] = $this->stores[$this->storeId]['cdn'] . $images[0];
                $tempProduct['url'] = $this->stores[$this->storeId]['host'] . 'products/' . $product->url_key;
            }

            $products[] = $tempProduct;
        }

        $this->quoteItems = $products;
    }

    function getProductFromGraphQL($query = null)
    {
        $headers = [];

        # tf graph queries need to supply a header
        if ($this->storeId == 12) {
            $headers['Store'] = 'www_1215diamonds_com';
        }

        $graphResponse = $this->guzzleClient->request('POST', $this->graphqlEndpoint, [
            'headers' => $headers,
            'json' => [
                'query' => $query
            ]
        ]);

        # check the post status code
        if ($graphResponse->getStatusCode() == 200) {
            $json = $graphResponse->getBody()->getContents();
            $body = json_decode($json);
            $graphResult = $body->data;
            $graphItems = $graphResult->products->items;

            if (count($graphItems) > 0) {
                return $graphItems[0];
            }
        }

        return [];
    }

    function getConfigurableProductBySku($sku = null)
    {
        $query = <<<GQL
        {
            products(filter: { sku: { in: ["$sku"] } }) {
                items {
                    name
                    url_key
                    media_gallery{
                        image_path
                        label
                        position
                    }
                    ... on ConfigurableProduct {
                        variants {
                            product {
                                id
                                sku
                                name
                                price_range {
                                    minimum_price {
                                        regular_price {
                                            value
                                        }
                                        discount {
                                        amount_off
                                        percent_off
                                        }
                                        final_price {
                                        value
                                        }
                                    }
                                    maximum_price {
                                        regular_price {
                                            value
                                        }
                                        discount {
                                            amount_off
                                            percent_off
                                        }
                                        final_price {
                                        value
                                        }
                                    }
                                }
                                special_price
                            }
                        }
                    }
                }
            }
        }
GQL;

        return $this->getProductFromGraphQL($query);
    }

    function getSimpleProductBySku($sku = null)
    {
        $query = <<<GQL
        {
            products(filter: { sku: { in: ["$sku"] } }) {
                items {
                    name
                    url_key
                    media_gallery{
                        image_path
                        label
                        position
                    }
                    price_range {
                        minimum_price {
                            regular_price {
                            value
                            currency
                            }
                            final_price {
                            value
                            currency
                            }
                        }
                    }
                    special_price
                }
            }
        }
GQL;

        return $this->getProductFromGraphQL($query);
    }

    function getStoneProductBySku($sku = null)
    {
        $productQuery = "SELECT
                            entity_id,
                            sku,
                            name.value name,
                            price.value price,
                            special_price.value special_price,
                            image.value as img,
                            shape.value shape
                        FROM
                            catalog_product_entity e
                        INNER JOIN
                            catalog_product_entity_decimal price on e.entity_id = price.row_id and price.store_id = 0 and price.attribute_id = 64
                        LEFT JOIN
                            catalog_product_entity_decimal special_price on e.entity_id = special_price.row_id and special_price.store_id = 0 and special_price.attribute_id = 65
                        LEFT JOIN
                            catalog_product_entity_varchar image on e.entity_id = image.row_id and image.store_id = 0 and image.attribute_id = 75
                        LEFT JOIN
                            catalog_product_entity_varchar name on e.entity_id = name.row_id and name.store_id = 0 and name.attribute_id = 60
                        LEFT JOIN
                            catalog_product_entity_int shape on e.entity_id = shape.row_id and shape.store_id = 0 and shape.attribute_id = 303
                        WHERE
                            e.sku = '" . $sku . "';";

        $products = $this->db->query($productQuery)->fetchAll(PDO::FETCH_OBJ);

        if ($products[0]->img != "no_selection" && $products[0]->img != "null") {
            $imagePath = $products[0]->img;
        } else {
            // get the shape of the stone and use that for image reference
            $shape = $this->shapeMap[$products[0]->shape];

            $imagePath = 'https://assets.1215diamonds.com/image/upload/w_300,c_scale/q_auto,f_auto/cut-' . $shape . '.png';
        }

        return (object) [
            'name' => $products[0]->name,
            'url_key' => 'stones?sku=' . $sku,
            'media_gallery' => [ (object) [
                'image_path' =>  $imagePath,
                'label' => '',
                'position' => 1
            ]],
            'price_range' => (object) [
                'minimum_price' => (object) [
                    'regular_price' => (object) [
                        'value' => $products[0]->price
                    ],
                    'final_price' => (object) [
                        'value' => $products[0]->price
                    ]
                ]
            ],
            'special_price' => null,
        ];
    }

    function getFormattedResult()
    {
        return json_encode([
            'url' => $this->getHost() . 'checkout/cart/rebuild/id/' . $this->quoteId,
            'products' => $this->quoteItems
        ]);
    }
}

$qId = $_GET['qid'];

if( $qId > 0 ) {
    $cartFeed = new CartFeed($qId);
    $result = $cartFeed->getFormattedResult($cartFeed);

    echo $result;
}
