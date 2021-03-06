<?php

ini_set("display_errors", false);

function getStoreConfig() {
    return [
        1 => [
            'graph' => 'https://www-api.diamondnexus.com/',
            'host' => 'https://www.diamondnexus.com/',
            'cdn' => 'https://assets.diamondnexus.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
        ],
        12 => [
            'graph' => 'https://www-api.1215diamonds.com/',
            'host' => 'https://www.1215diamonds.com/',
            'cdn' => 'https://assets.1215diamonds.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
        ],
        11 => [
            'graph' => 'https://www-api.foreverartisans.com/',
            'host' => 'https://www.foreverartisans.com/',
            'cdn' => 'https://assets.foreverartisans.com/image/upload/w_300,c_scale/q_auto,f_auto/media/catalog/product'
        ]
    ];
}

function getPdoConnection() {
    global $env;

    $dbName = $env['db']['connection']['default']['dbname'];
    $dbHost = $env['db']['connection']['default']['host'];
    $dbUser = $env['db']['connection']['default']['username'];
    $dbPass = $env['db']['connection']['default']['password'];

    return new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
}

$pids = [];
$pidsAry =[];
$productSkuList = '';

# get env variables for host and elastic
$env  = include('../../../app/etc/env.php');

# load magento classes from vendor
require '/var/www/magento/app/bootstrap.php';

$client = new \GuzzleHttp\Client([
    'verify' => false
]);

// adding support for user friendly brand abbreviations vs store ids
$brand = (!empty($_REQUEST['brand'])) ? $_REQUEST['brand'] : 'dn';
$storeIdsAry = ['dn'=>1,'fa'=>11,'tf'=>12,'1215'=>12];
$storeId = $storeIdsAry[$brand];

if ($_REQUEST['pids'] && !empty($storeId)) {
    $pids = $_REQUEST['pids'];
} else {
    return false;
}
$pidsAry = json_decode($pids);

// get store configurations
$hostConfig = getStoreConfig();

# parse url parts
$url = parse_url($hostConfig[$storeId]['graph']);

# www requests have to be over non ssl connection
$graphqlEndpoint = $hostConfig[$storeId]['graph'] . "graphql/";

$db = getPdoConnection();
$productQuery = "SELECT sku FROM catalog_product_entity WHERE entity_id IN(" . implode(",", $pidsAry) . ");" ;
$productResult = $db->query($productQuery)->fetchAll(PDO::FETCH_ASSOC);

$result = [];

foreach($productResult as $product) {

    # get product sku unique so response is cached vs permutations
    $sku = '"' . $product['sku'] . '"';

    $query = <<<GQL
    {
      products(filter: { sku: { in: [$sku] } }) {
        items {
            id
            type_id
            name
            shape
            attribute_set_id
            url_key
            media_gallery{
                image_path
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

    $headers = [];

    # tf graph queries need to supply a header
    if ($storeId == 12) {
        $headers['Store'] = 'www_1215diamonds_com';
    }

    $graphResponse = $client->request('POST', $graphqlEndpoint, [
        'headers' => $headers,
        'json' => [
            'query' => $query
        ]
    ]);

    # check the post status code
    if($graphResponse->getStatusCode() == 200) {

        $json = $graphResponse->getBody()->getContents();
        $body = json_decode($json);
        $graphResult = $body->data;
        $graphItems = $graphResult->products->items;

        //print_r($json);

        foreach ($graphItems as $product) {
            # default image
            $productImage = $product->media_gallery[0]->image_path;

            $regularPrice = $product->price_range->minimum_price->regular_price->value;
            $specialPrice = 0;

            foreach($product->media_gallery as $image) {
                if($image->position == 1) {
                    $productImage = $image->image_path;
                }
            }

            # special pricing overrides catalog price rules
            if ($product->special_price != null) {
                $specialPrice = $product->special_price;
            } else {
                $specialPrice = $product->price_range->minimum_price->final_price->value;
            }

            $result[] = [
                'id' => $product->id,
                'type_id' => $product->type_id,
                'name' => $product->name,
                'shape' => $product->shape[0],
                'price' => $regularPrice,
                'special_price' => $specialPrice,
                'attribute_set_id' => $product->attribute_set_id,
                'url' => $hostConfig[$storeId]['host'] . 'products/' . $product->url_key,
                'img' => $hostConfig[$storeId]['cdn'] . $productImage
            ];
        }
    }
}

if(count($result) > 0) {
    echo json_encode(['products' => $result]);
} else {
    echo "Sorry, there was a problem generating feed.";
}
