<?php

namespace ForeverCompanies\CronJobs\Cron;

require_once $_SERVER['HOME'].'magento/shell/dnl/encoding.php';
require_once $_SERVER['HOME'].'magento/shell/dnl/google_api/ProductsFeed.php';

use Psr\Log\LoggerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Model 
{
    
    protected $logger;
    protected $storeRepository;
    protected $storeManager;

    public function __construct(
        LoggerInterface $logger,
        StoreRepositoryInterface $storeRepositoryInterface,
        StoreManagerInterface $storeManagerInterface
        ) {
        $this->logger = $logger;
        
        $this->storeRepository = $storeRepositoryInterface;
        $this->storeManager = $storeManagerInterface;
    }

   /**
    * Write to system.log
    *
    * @return void
    */
    public function execute() {
        $this->logger->info('Cron Works~');
    }
    
    function check_format($text) {
        $encoding = new Encoding;
        return recode_string("us..flat",$encoding->fixALL($text));
    }
    
    function min_display($arrayName) {
        $udisplay = '';
        $udisplay = min(array_unique($arrayName));
        return $udisplay;
    }
    
    function unique_display($arrayName, $delimter=' / ') {
        $udisplay = '';
        foreach (array_unique($arrayName) as $Name) {
            $udisplay .= $Name.$delimter;
        }
        $udisplay = substr($udisplay,0,-(strlen($delimter)));
        if(strlen($udisplay) == 0) {
            $udisplay = "May Vary";
        }
        return $udisplay;
    }
    
    function fileheader() {
        // Header
        return array("setAdwordsGrouping","setAgeGroup","setBrand","setCondition","setDescription","setGender","setImageLink","setProductLink","setPrice","setMpn","setShippingWeight","setColor","setMaterial","addSize","setTitle","setGoogleProductCategory","setAtomId","setAvailability","setItemGroupId","setProductType","setSKU", "setSalePrice","sale_price_effective_date","Recommendable","custom label 1","custom label 2","custom label 3");
    }
    
    function fileheaderYahoo() {
        // Header
        return array("bingads_grouping","AgeGroup","Brand","Condition","Description","Gender","ImageURL","ProductURL","Price","MPN","ShippingWeight","Color","Material","addSize","Title","MerchantCategory","MPID","Availability","GroupId","ProductType","SKU","pricewithdiscount","sale_price_effective_date","custom_label_0","custom_label_1","custom_label_2","custom_label_3");
    }
    
    function fileheaderFB() {
        // Header
        return array("id","availability","condition", "description", "image_link", "link", "title", "price", "mpn", "sale_price", "sale_price_effective_date");
    }
    
    function getAllProductList() {
        // Build Profile
   //     $storeId = Mage::app()->getStore($GLOBALS['argvStoreId']);
   //     Mage::app()->setCurrentStore($storeId);
        
        $this->storeManager->setCurrentStore($GLOBALS['argvStoreId']);
        
        $products = Mage::getResourceModel('catalog/product_collection');
        $products->joinField('store_id', 'catalog_category_product_index', 'store_id', 'product_id=entity_id', '{{table}}.store_id = '.$GLOBALS['argvStoreId'], 'left');
        $products->getSelect()->distinct(true);
        $products->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);
        $products->addFieldToFilter('visibility',array('4'));
        $products->addFieldToFilter('is_in_stock',0);
        
        $product_count = count($products);
        foreach ($products as $product) {
            $prebuilts[] = displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    
}