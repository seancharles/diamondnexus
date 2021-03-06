<?php
declare(strict_types=1);

namespace ForeverCompanies\LooseStonesQuery\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class StonesQuery implements ResolverInterface
{
    protected $productColl;
    protected $totalCountColl;
    
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFac
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productColl = $collectionFac->create();
        
        // TODO: Figure out a better way to get the total count, if possible.
        $this->totalCountColl = $collectionFac->create();
    }
    
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $onlineEqFilter = $args['filter']['online']['eq'];
        $productTypeEqFilter = $args['filter']['fc_product_type']['eq'];
        $colorInFilter = $args['filter']['color']['in'];
        $clarityInFilter = $args['filter']['clarity']['in'];
        $caratWeightFromFilter = $args['filter']['carat_weight']['from'];
        $caratWeightToFilter = $args['filter']['carat_weight']['to'];
        $priceFromFilter = $args['filter']['price']['from'];
        $priceToFilter = $args['filter']['price']['to'];
        $cutGradeInFilter = $args['filter']['cut_grade']['in'];
        $shapeInFilter = $args['filter']['shape']['in'];
        $pageSize = $args['pageSize'];
        $currentPage = $args['currentPage'];
        
        foreach ($args['sort'] as $k => $v) {
            $sortKey = $k;
            $sortVal = $v;
        }
        
        $this->productColl->setOrder($sortKey, $sortVal);
        
        $this->productColl->addAttributeToFilter('online', $onlineEqFilter);
        $this->productColl->addAttributeToFilter('fc_product_type', $productTypeEqFilter);
        $this->productColl->joinField(
            'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
        );
        $this->productColl->addAttributeToFilter('qty', ['gt' => 0]);
        
        if (!empty($colorInFilter)) {
            $this->productColl->addAttributeToFilter('color', array('in' => $colorInFilter));
            $this->totalCountColl->addAttributeToFilter('color', array('in' => $colorInFilter));
        }
        if (!empty($clarityInFilter)) {
            $this->productColl->addAttributeToFilter('clarity', array('in' =>  $clarityInFilter));
            $this->totalCountColl->addAttributeToFilter('clarity', array('in' =>  $clarityInFilter));
        }
        if (!empty($cutGradeInFilter)) {
            $this->productColl->addAttributeToFilter('cut_grade', array('in' =>  $cutGradeInFilter));
            $this->totalCountColl->addAttributeToFilter('cut_grade', array('in' =>  $cutGradeInFilter));
        }
        if (!empty($shapeInFilter)) {
            $this->productColl->addAttributeToFilter('shape', array('in' =>  $shapeInFilter));
            $this->totalCountColl->addAttributeToFilter('shape', array('in' =>  $shapeInFilter));
        }
        
        $this->productColl->addAttributeToFilter('carat_weight', array('gteq' =>  $caratWeightFromFilter));
        $this->productColl->addAttributeToFilter('carat_weight', array('lteq' =>  $caratWeightToFilter));
        $this->productColl->addAttributeToFilter('price', array('gteq' =>  $priceFromFilter));
        $this->productColl->addAttributeToFilter('price', array('lteq' =>  $priceToFilter));
        $this->productColl->addAttributeToFilter('status', array('eq' =>  1));
        
        $this->totalCountColl->addAttributeToFilter('online', $onlineEqFilter);
        $this->totalCountColl->addAttributeToFilter('fc_product_type', $productTypeEqFilter);
        
        $this->totalCountColl->joinField(
            'qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'
            );
        $this->totalCountColl->addAttributeToFilter('qty', ['gt' => 0]);
        
        $this->totalCountColl->addAttributeToFilter('carat_weight', array('gteq' =>  $caratWeightFromFilter));
        $this->totalCountColl->addAttributeToFilter('carat_weight', array('lteq' =>  $caratWeightToFilter));
        $this->totalCountColl->addAttributeToFilter('price', array('gteq' =>  $priceFromFilter));
        $this->totalCountColl->addAttributeToFilter('price', array('lteq' =>  $priceToFilter));
        $this->totalCountColl->addAttributeToFilter('status', array('eq' =>  1));
        $this->totalCountColl->load();
        
        $this->productColl->setPageSize($pageSize);
        $this->productColl->setCurPage($currentPage);
        $this->productColl->addAttributeToSelect("*")->load();
        
        $totalCount = count($this->totalCountColl);
        
        $items = array();
        $items['data'] = array();
        $items['data']['products'] = array();
        $items['data']['products']['total_count'] = $totalCount;
        $items['data']['products']['page_info'] = array();
        $items['data']['products']['page_info']['total_pages'] = ceil($totalCount /  $pageSize);
        
        $items['data']['products']['sort_fields'] = array();
        $items['data']['products']['sort_fields']['default'] = 'position';
        $items['data']['products']['sort_fields']['options'] = [];
        $items['data']['products']['sort_fields']['options'][] = array("label" => "position", "value" => "position");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Product name", "value" => "name");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Price", "value" => "price");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Shape", "value" => "shape");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Carat Weight", "value" => "carat_weight");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Clarity Sort", "value" => "clarity_sort");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Color Sort", "value" => "color_sort");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Cut Grade Sort", "value" => "cut_grade_sort");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Shape Alpha Sort", "value" => "shape_alpha_sort");
        $items['data']['products']['sort_fields']['options'][] = array("label" => "Shape Popularity Sort", "value" => "shape_pop_sort");
        
        $items['data']['products']['aggregations'] = [];
        
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "price",
            "label" => "Price",
            "count" => 10,
            "options" => [
                array("value" => "0_10000", "label" => "0-10000"),
                array("value" => "10000_20000", "label" => "10000-20000"),
                array("value" => "20000_30000", "label" => "20000-30000"),
                array("value" => "30000_40000", "label" => "30000-40000"),
                array("value" => "40000_50000", "label" => "40000-50000"),
                array("value" => "50000_60000", "label" => "50000-60000"),
                array("value" => "60000_70000", "label" => "60000-70000"),
                array("value" => "70000_80000", "label" => "70000-80000"),
                array("value" => "80000_90000", "label" => "80000-90000"),
                array("value" => "90000_100000", "label" => "90000-100000")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "category_id",
            "label" => "Category",
            "count" => 1,
            "options" => [
                array("value" => "906", "label" => "Clearance Lab Grown Diamonds")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "color",
            "label" => "Color",
            "count" => 7,
            "options" => [
                array("value" => "2870", "label" => "I"),
                array("value" => "2868", "label" => "G"),
                array("value" => "2869", "label" => "H"),
                array("value" => "2867", "label" => "F"),
                array("value" => "2866", "label" => "E"),
                array("value" => "2865", "label" => "D"),
                array("value" => "2871", "label" => "J")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "clarity",
            "label" => "Clarity",
            "count" => 7,
            "options" => [
                array("value" => "2859", "label" => "VS1"),
                array("value" => "2861", "label" => "VS2"),
                array("value" => "2857", "label" => "SI1"),
                array("value" => "2863", "label" => "VVS2"),
                array("value" => "2862", "label" => "VVS1"),
                array("value" => "2858", "label" => "SI2"),
                array("value" => "2854", "label" => "IF")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "cut_grade",
            "label" => "Cut",
            "count" => 5,
            "options" => [
                array("value" => "2877", "label" => "Ideal"),
                array("value" => "2876", "label" => "Excellent"),
                array("value" => "2878", "label" => "Very Good"),
                array("value" => "3076", "label" => "Not Specified"),
                array("value" => "2879", "label" => "Good")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "shape",
            "label" => "Shape",
            "count" => 10,
            "options" => [
                array("value" => "2842", "label" => "Round"),
                array("value" => "2847", "label" => "Oval"),
                array("value" => "2850", "label" => "Pear"),
                array("value" => "2848", "label" => "Emerald"),
                array("value" => "2845", "label" => "Cushion"),
                array("value" => "2843", "label" => "Princess"),
                array("value" => "2849", "label" => "Radiant"),
                array("value" => "2844", "label" => "Asscher"),
                array("value" => "2846", "label" => "Heart"),
                array("value" => "2851", "label" => "Marquise")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "carat_weight_bucket",
            "label" => "carat_weight_bucket",
            "count" => 1,
            "options" => [
                array("value" => "0_100", "label" => "0_100")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "online",
            "label" => "Displayed on Frontend",
            "count" => 1,
            "options" => [
                array("value" => "3448", "label" => "Yes")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "clarity_sort_bucket",
            "label" => "clarity_sort_bucket",
            "count" => 7,
            "options" => [
                array("value" => "400", "label" => "400"),
                array("value" => "300", "label" => "300"),
                array("value" => "200", "label" => "200"),
                array("value" => "500", "label" => "500"),
                array("value" => "600", "label" => "600"),
                array("value" => "100", "label" => "100"),
                array("value" => "700", "label" => "700")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "color_sort_bucket",
            "label" => "color_sort_bucket",
            "count" => 7,
            "options" => [
                array("value" => "200", "label" => "200"),
                array("value" => "400", "label" => "400"),
                array("value" => "300", "label" => "300"),
                array("value" => "500", "label" => "500"),
                array("value" => "600", "label" => "600"),
                array("value" => "700", "label" => "700"),
                array("value" => "100", "label" => "100")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "cut_grade_sort_bucket",
            "label" => "cut_grade_sort_bucket",
            "count" => 5,
            "options" => [
                array("value" => "400", "label" => "400"),
                array("value" => "300", "label" => "300"),
                array("value" => "200", "label" => "200"),
                array("value" => "500", "label" => "500"),
                array("value" => "100", "label" => "100")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "shape_alpha_sort_bucket",
            "label" => "shape_alpha_sort_bucket",
            "count" => 10,
            "options" => [
                array("value" => "1000", "label" => "1000"),
                array("value" => "600", "label" => "600"),
                array("value" => "700", "label" => "700"),
                array("value" => "300", "label" => "300"),
                array("value" => "200", "label" => "200"),
                array("value" => "800", "label" => "800"),
                array("value" => "900", "label" => "900"),
                array("value" => "100", "label" => "100"),
                array("value" => "400", "label" => "400"),
                array("value" => "500", "label" => "500")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "shape_pop_sort_bucket",
            "label" => "shape_pop_sort_bucket",
            "count" => 10,
            "options" => [
                array("value" => "100", "label" => "100"),
                array("value" => "400", "label" => "400"),
                array("value" => "600", "label" => "600"),
                array("value" => "500", "label" => "500"),
                array("value" => "300", "label" => "300"),
                array("value" => "200", "label" => "200"),
                array("value" => "800", "label" => "800"),
                array("value" => "700", "label" => "700"),
                array("value" => "1000", "label" => "1000"),
                array("value" => "900", "label" => "900")
            ]
        );
        $items['data']['products']['aggregations'][] = array(
            "attribute_code" => "fc_product_type",
            "label" => "Product Type",
            "count" => 1,
            "options" => [
                array("value" => "3569", "label" => "Diamond")
            ]
        );
        
        $items['data']['products']['items'] = [];
        $i = 0;
        
        foreach ($this->productColl as $_product) {
            
            $productArr = array();
            
            $productArr['__typename'] = ucfirst($_product->getTypeId()) . "Product";
            $productArr['id'] = $_product->getId();
            $productArr['sku'] = $_product->getSku();
            $productArr['cert_url_key'] = $_product->getCertUrlKey();
            $productArr['video_url'] = $_product->getVideoUrl();
            
            $productArr['url_key'] = $_product->getUrlKey();
            $productArr['name'] = $_product->getName();
            $productArr['color'] = array($_product->getColor() . ", "
                . $_product->getResource()->getAttribute('color')->getFrontend()->getValue($_product));
            $productArr['color_sort'] = $_product->getColorSort();
            $productArr['clarity'] = array($_product->getColor() . ", "
                . $_product->getResource()->getAttribute('clarity')->getFrontend()->getValue($_product));
            $productArr['clarity_sort'] = $_product->getClaritySort();
            $productArr['carat_weight'] = $_product->getCaratWeight();
            $productArr['cut_grade'] = array($_product->getColor() . ", "
                . $_product->getResource()->getAttribute('cut_grade')->getFrontend()->getValue($_product));
            $productArr['cut_grade_sort'] = $_product->getCutGradeSort();
            $productArr['shape'] = array($_product->getColor() . ", "
                . $_product->getResource()->getAttribute('shape')->getFrontend()->getValue($_product));
            
            $productArr['price_range'] = array();
            $productArr['price_range']['minimum_price'] = array();
            $productArr['price_range']['minimum_price']['final_price'] = array();
            $productArr['price_range']['minimum_price']['final_price']['currency'] = "USD";
            $productArr['price_range']['minimum_price']['final_price']['value'] = round($_product->getPrice(), 2);
            $productArr['price_range']['maximum_price'] = array();
            $productArr['price_range']['maximum_price']['final_price'] = array();
            $productArr['price_range']['maximum_price']['final_price']['currency'] = "USD";
            $productArr['price_range']['maximum_price']['final_price']['value'] = round($_product->getPrice(), 2);
            
            $productArr['media_gallery'] = array();
            
            $items['data']['items'][]  = $productArr;
        }
        echo json_encode($items);die;
    }
    
}
