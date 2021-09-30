<?php

ini_set("display_errors", true);

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

# get base url for host
$baseUrl = $env['system']['default']['web']['secure']['base_url'];

# must have trailing slash or you will get 401 errors
$graphqlEndpoint = $baseUrl . "graphql/";

$client = new \GuzzleHttp\Client();

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
        }
      }
    }
GQL;

    $graphResponse = $client->request('POST', $graphqlEndpoint, [
        'headers' => [
            // include any auth tokens here
        ],
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

        //print_r($items);

        foreach ($graphItems as $product) {
            $result[] = [
                'id' => $product->id,
                'type_id' => $product->type_id,
                'name' => $product->name,
                'shape' => $product->shape[0],
                'price' => $product->price_range->minimum_price->regular_price->value,
                'special_price' => $product->price_range->minimum_price->final_price->value,
                'attribute_set_id' => $product->attribute_set_id,
                'url' => $hostConfig[$storeId]['host'] . 'products/' . $product->url_key,
                'img' => $hostConfig[$storeId]['cdn'] . $product->media_gallery[0]->image_path
            ];
        }
    }
}

if(count($result) > 0) {
    echo json_encode(['products' => $result]);
} else {
    echo "Sorry, there was a problem generating feed.";
}