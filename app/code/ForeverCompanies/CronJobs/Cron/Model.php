<?php

namespace ForeverCompanies\CronJobs\Cron;

require_once $_SERVER['HOME'].'magento/shell/dnl/encoding.php';
require_once $_SERVER['HOME'].'magento/shell/dnl/google_api/ProductsFeed.php';

use Psr\Log\LoggerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

class Model 
{
    
    protected $logger;
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;

    public function __construct(
        LoggerInterface $logger,
        StoreRepositoryInterface $storeRepositoryInterface,
        StoreManagerInterface $storeManagerInterface,
        CollectionFactory $collectionFactory,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo
        ) {
        $this->logger = $logger;
        
        $this->storeRepository = $storeRepositoryInterface;
        $this->storeManager = $storeManagerInterface;
        $this->productCollectionFactory = $collectionFactory;
        $this->productModel = $productFactory;
        $this->resourceConnection = $resource;
        $this->attributeSetMod = $attributeSetRepo;
        
        $this->stockItemModel = $stockItemRepo;
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
        
        
        $products = $this->productCollectionFactory->create()->setFlag('has_stock_status_filter', false)->load();
        // https://github.com/magento/magento2/issues/15187
        $products->joinField('store_id', 'catalog_category_product_index', 'store_id', 'product_id=entity_id', '{{table}}.store_id = '.$GLOBALS['argvStoreId'], 'left');
        $products->getSelect()->distinct(true);
        $products->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);
        $products->addFieldToFilter('visibility',array('4'));
        
        $productArr = array();
        
        foreach ($products as $product) {
            try {
                $stockItem = $this->stockItemModel->get($product->getId());
            }
            catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
            if ($stockItem->getIsInStock() == 1){
                continue;
            }
            $productArr[] = $product;
        }
        
        $product_count = count($productArr);
        foreach ($productArr as $product) {
            $prebuilts[] = displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    function getRecentProductList() {
        $date = date('Y-m-d', strtotime("now - 1 day"));
        // Build Profile
        $this->storeManager->setCurrentStore($GLOBALS['argvStoreId']);
        
        $products = $this->productCollectionFactory->create()->setFlag('has_stock_status_filter', false)->load();
        $products->addAttributeToSelect('*');
        $products->addAttributeToFilter('updated_at', array('gteq' => $date));
        $products->joinField('store_id', 'catalog_category_product_index', 'store_id', 'product_id=entity_id', '{{table}}.store_id = '.$GLOBALS['argvStoreId'], 'left');
        $products->getSelect()->distinct(true);
        $products->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);
        $products->addFieldToFilter('visibility',array('4'));
        
        $productArr = array();
        
        foreach ($products as $product) {
            try {
                $stockItem = $this->stockItemModel->get($product->getId());
            }
            catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
            if ($stockItem->getIsInStock() == 1){
                continue;
            }
            $productArr[] = $product;
        }
        
        $product_count = count($productArr);
        foreach ($productArr as $product) {
            $prebuilts[] = displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    function getOneProduct($pid) {
        // Build Profile
        $products = $this->productModel->create()->load($pid);
        $product_count = count($products);
        print_r($products);
        foreach ($products as $product) {
            $prebuilts[] = displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    function getProdImage($product,$pid,$metal) {
        $fcDbRead = $this->resourceConnection->getConnection();
        $fcDbQuery = 'select
		        mg.*,
		        mgv.store_id,
		        mgv.label
		    from
		        catalog_product_entity_media_gallery AS mg,
		        catalog_product_entity_media_gallery_value AS mgv
		    where
		        mg.value_id = mgv.value_id
		    and
		        mg.entity_id = ' . $pid . '
		    and
		        mgv.store_id IN (0,1)
		    and
		        mgv.label LIKE "%' . $metal . '%"
		    and
		        mgv.label LIKE "%Default%"
		    order by
		        store_id,
		        entity_id,
		        position
		    limit 1;';
        
        $fcDbQueryResults = $fcDbRead->fetchAll($fcDbQuery);
        
        if( strlen($fcDbQueryResults[0]['value']) > 0 ) {
        //    $image = $product->getMediaConfig()->getMediaUrl($fcDbQueryResults[0]['value']);
            $image = $this->storeManager->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $fcDbQueryResults[0]['value'];
        } else {
        //    $image = $product->getMediaConfig()->getMediaUrl($product->getData('image'));
            $image = $this->storeManager->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        }
        return $image;
    }
    
    function displayProd($productModel) {
        // Get Product
        $product = $this->productModel->create()
        ->setStoreId($GLOBALS['argvStoreId'])
        ->load($productModel->getId());
        // Get Attributes
        
        if ($product->getVisibility() != 4) {
            return;
        }
        if ($product->getStatus() != 1) {
            return;
        }
        $recom = 'True';
        $attributeSetModel = $this->attributeSetMod->get($product->getAttributeSetId());
        $attributeSetName  = $attributeSetModel->getAttributeSetName();
        // Get Options? (Ring Size)
        $options_cus = $product->getOptions();
        
        $pid = $product->getId();
        $sku = $product->getSku();
        $name = $product->getName();
        $status = $product->getStatus();
        $visiblility = $product->getVisibility();
        $url =  $product->getUrlModel()->getUrl($product, array('_ignore_category'=>true));
        $main_image = $this->storeManager->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        $sale_start_uf = new DateTime($product->getSpecialFromDate());
        $sale_start = $sale_start_uf->format(DateTime::ATOM);
        $sale_end_uf = new DateTime($product->getSpecialToDate());
        $sale_end = $sale_end_uf->format(DateTime::ATOM);
        $description = preg_replace('/\s+/S', " ", check_format(strip_tags($product->getDescription())));
        $title = $name;
        $Category = "Apparel & Accessories > Jewelry";
        $ProductType = $Category;
        $high_margin = '';$bestseller = '';
        if (preg_match('/No/',$product->getAttributeText("high_margin"))) {
            $high_margin = "High Margin";
        }
        if (preg_match('/No/',$product->getAttributeText("bestseller"))) {
            $bestseller = "Bestseller";
        }
        switch (substr($sku, 0, 1)) {
            case "L":
                $Gender = "female";
                break;
            case "M":
                $Gender = "male";
                break;
            default:
                $Gender = "unisex";
                break;
        }
        
        switch($attributeSetName) {
            case "Rings":
            case "Ring":
            case "Mens Rings":
            case "Matching Bands":
            case "Pure Carbon Rings":
                switch (substr($sku, 10, 1)) {
                    case 'X':
                        $StoneTypeExtended = " > Diamond Simulants";
                        break;
                    case 'B':
                        $StoneTypeExtended = " > Matching Band";
                        break;
                    case 'C':
                        $StoneTypeExtended = " > Lab Diamonds";
                        break;
                    default:
                        $StoneTypeExtended = "";
                        break;
                }
                switch (substr($sku, 4, 2)) {
                    case '3S':
                        // 3S = "Three Stone";
                        $ProductTypeExtended = " > Three Stone";
                        break;
                    case 'MS':
                        // MS = "Multi-stone"
                        $ProductTypeExtended = " >  Multi-stone";
                        break;
                    case 'OR':
                        // OR = "Ornate Styles"
                        $ProductTypeExtended = " > Ornate Styles";
                        break;
                    case 'SA':
                        // SA = "Simply Accented Solitaires"
                        $ProductTypeExtended = " > Simply Accented Solitaires";
                        break;
                    case 'SL':
                        // SL = "Classic Solitaires"
                        $ProductTypeExtended = " > Classic Solitaires";
                        break;
                    case 'VT':
                        // VT = "Vintage"
                        $ProductTypeExtended = " > Vintage";
                        break;
                    default:
                        $ProductTypeExtended = "";
                        break;
                }
                switch (substr($sku, 0, 4)) {
                    case 'LREN':
                        $title = $name;
                        $Category .= " > Rings";
                        $ProductType .= " > Rings > Engagement".$ProductTypeExtended;
                        break;
                    default:
                        $title = $name;
                        $Category .= " > Rings";
                        $ProductType .= " > Rings";
                        break;
                }
                break;
            case "Bracelets":
                $Category .= " > Bracelets";
                $ProductType .= " > Bracelets";
                break;
            case "Necklaces":
                $Category .= " > Necklaces";
                $ProductType .= " > Necklaces";
                break;
            case "Earrings":
                $Category .= " > Earrings";
                $ProductType .= " > Earrings";
                break;
            case "Pendants":
                $Category .= " > Charms & Pendants";
                $ProductType .= " > Charms & Pendants";
                break;
            case "Loose Stones":
                $Category .= " > Precious Stones";
                $ProductType .= " > Precious Stones";
                $recom = 'False';
                break;
            case "Watches":
                $Category .= " > Watches";
                $ProductType .= " > Watches";
                break;
            default:
                $Category .= "";
                $ProductType .= "";
                break;
        }
        
        if (preg_match('/JCLEANER/', $sku)) {
            $recom = 'False';
        }
        // Custom Options
        $Sizes = array();
        foreach ($options_cus as $option) {
            switch($option->getTitle()) {
                case 'Ring Size':
                case 'Band Width':
                case 'Chain Length':
                case 'Chain Width':
                    foreach ($option->getValues() as $opt) {
                        $Sizes[] = $opt->getTitle();
                    }
                    break;
                default:
                    break;
            }
        }
        $size_display = "";
        if (count(unique_display($Sizes)) > 0) {
            $size_display = "Varies";
        }
        $title = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',$title)));
        
        $Cuts = array();
        $Prices = array();
        $Colors = array();
        $Metals = array();
        $Gemstones = array();
        $SalePrices = array();
        switch($product->getTypeId()) {
            case 'configurable':
                $itemGroupId = $sku;
                $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null,$product);
                $childs = array();
                foreach ($childProducts as $simpleModel) {
                    // Future reference displayProd($simpleModel); will generate the simple
                    $_product = Mage::getModel('catalog/product')->setStoreId($GLOBALS['argvStoreId'])->load($simpleModel->getId());
                    if($_product->getStatus() == 1) {
                        $SPrice = $_product->getPrice();
                        $sale_start_uf = new DateTime($_product->getSpecialFromDate());
                        $ssale_start = $sale_start_uf->format(DateTime::ATOM);
                        $sale_end_uf = new DateTime($_product->getSpecialToDate());
                        $ssale_end = $sale_end_uf->format(DateTime::ATOM);
                        $today = date('Y-m-dT00:00:00+00:00');
                        
                        if (($today >= $ssale_start) && ($today < $ssale_end) && ($_product->getPrice() != $_product->getFinalPrice())) {
                            $SSalePrice = $_product->getFinalPrice();
                            $onsale = "On Sale";
                        } else {
                            $SSalePrice = "";
                        }
                        $SCut = ($_product->getAttributeText('cut_type') == '') ? "None" : $_product->getAttributeText('cut_type');
                        $SColor = ($_product->getAttributeText('color') == '') ? "None" : $_product->getAttributeText('color');
                        $SMetal = ($_product->getAttributeText('metal_type') == '') ? "None" : $_product->getAttributeText('metal_type');
                        $SGemstone = ($_product->getAttributeText('gemstone') == '') ? "None" : $_product->getAttributeText('gemstone');
                        
                        $image = getProdImage($product,$product->getId(),$SMetal);
                        
                        $Cuts[] = $SCut;
                        $Prices[] = $SPrice;
                        $Colors[] = $SColor;
                        $Metals[] = $SMetal;
                        $Gemstones[] = $SGemstone;
                        $SalePrices[] = $SSalePrice;
                        $simpleTitleMods = $SGemstone." ".$SCut." ".$SColor." ".$SMetal;
                        $simpleTitle = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',preg_replace('/ None/','',$title." ".$simpleTitleMods))));
                        $childs[] = array($attributeSetName,"Adult", getWebsiteName(),"New",$description,$Gender,$image,$url."?precious-metal=".preg_replace('/ /','-',$SMetal)."&cid=".$_product->getId(),$SPrice,$_product->getSku(),"1",$SColor,$SMetal,$size_display,$simpleTitle,$Category,$_product->getId(),"In Stock",$itemGroupId,$ProductType,$sku,$SSalePrice,$ssale_start,$ssale_end,$recom,$onsale,$bestseller,$high_margin);
                    }
                }
                $price = min_display($Prices);
                $saleprice = min_display($SalePrices);
                break;
            case 'simple':
                $itemGroupId = false;
                $price = $product->getPrice();
                $sale_start_uf = new DateTime($product->getSpecialFromDate());
                $ssale_start = $sale_start_uf->format(DateTime::ATOM);
                $sale_end_uf = new DateTime($product->getSpecialToDate());
                $ssale_end = $sale_end_uf->format(DateTime::ATOM);
                $today = date('Y-m-dT00:00:00+00:00');
                if (($today >= $ssale_start) && ($today < $ssale_end) && ($product->getPrice() != $product->getFinalPrice())) {
                    $saleprice = $product->getFinalPrice();
                    $onsale = "On Sale";
                } else {
                    $saleprice = "";
                }
                $Cuts[] = $product->getAttributeText('cut_type');
                $Colors[] = $product->getAttributeText('color');
                $Metals[] = $product->getAttributeText('metal_type');
                $image = getProdImage($product,$product->getId(),$product->getAttributeText('metal_type'));
                $Gemstones[] = $product->getAttributeText('gemstone');
                $simpleTitleMods = $Gemstones[0]." ".$Cuts[0]." ".$Colors[0]." ".$Metals[0];
                $title = preg_replace('~[[:cntrl:]]~','',preg_replace('/(\s)+/',' ',preg_replace('/s$/','',preg_replace('/ None/','',$title." ".$simpleTitleMods))));
                break;
            default:
                break;
        }
        
        $cuts_display = "";
        if (count(unique_display($Cuts)) > 0) {
            $cuts_display = "Varies";
        }
        $color_display = "";
        if (count(unique_display($Colors)) > 0) {
            $color_display = "Varies";
        }
        $metal_display = "";
        if (count(unique_display($Metals)) > 0) {
            $metal_display = "Varies";
        }
        $gems_display = "";
        if (count(unique_display($Gemstones)) > 0) {
            $gems_display = "Varies";
        }
        
        // Checks
        if((strlen($description) > 2000) || (strlen($description) == 0)) {
            //print '"'.$pid.'","'.$sku.'","description too short"'."\n";
            return;
        } elseif (preg_match('/USLSPC/', $sku)) {
            return;
        } elseif ($price == 0) {
            //print '"'.$pid.'","'.$sku.'","Zero Price"'."\n";
            return;
        } elseif (strlen($color_display) == 0) {
            //print '"'.$pid.'","'.$sku.'","Color Options?"'."\n";
            return;
        } elseif (preg_match('/_ignore_category/', $url)) {
            //print '"'.$pid.'","'.$sku.'","_ignore_category in url is bad sign"'."\n";
            return;
        } elseif (preg_match('/[0-9].html/', $url) && (!(preg_match('/\-ring\-size/', $url)))) {
            //print '"'.$pid.'","'.$sku.'","'.$url.'","If not esteal something is bad."'."\n";
            return;
        } elseif (!(isset($sku))) {
            //print " no sku\n";
            return;
        } else {
            return array('Result' => array($attributeSetName,"Adult", getWebsiteName(),"New",$description,$Gender,$main_image,$url,$price,$sku,"1",$color_display,$metal_display,$size_display,$title,$Category,$pid,"In Stock",$itemGroupId,$ProductType,$sku,$saleprice,$sale_start,$sale_end,$recom,$onsale,$bestseller,$high_margin), 'Children' => $childs);
        }
    }
    
    
}