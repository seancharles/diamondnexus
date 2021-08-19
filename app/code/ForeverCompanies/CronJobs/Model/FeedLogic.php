<?php
namespace ForeverCompanies\CronJobs\Model;

require_once $_SERVER['HOME'].'magento/shell/dnl/google_api/ProductsFeed.php';

use Psr\Log\LoggerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use ForeverCompanies\CronJobs\Dnl\Encoding;
use ForeverCompanies\ProductUrlPrefix\Model\Url as ProductUrlPrefixModel;

class FeedLogic
{
    protected $logger;
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;
    protected $reviewModel;
    protected $reviewCollection;
    protected $encoder;
    protected $productUrlPrefixModel;
    protected $invalidValueArr;

    public function __construct(
        LoggerInterface $logger,
        StoreRepositoryInterface $storeRepositoryInterface,
        StoreManagerInterface $storeManagerInterface,
        CollectionFactory $collectionFactory,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo,
        Review $rev,
        ReviewCollectionFactory $coll,
        Encoding $enc,
        ProductUrlPrefixModel $productUrlPrefixM
    ) {
        $this->logger = $logger;
        $this->storeRepository = $storeRepositoryInterface;
        $this->storeManager = $storeManagerInterface;
        $this->productCollectionFactory = $collectionFactory;
        $this->productModel = $productFactory;
        $this->resourceConnection = $resource;
        $this->attributeSetMod = $attributeSetRepo;
        $this->stockItemModel = $stockItemRepo;
        $this->reviewModel = $rev;
        $this->reviewCollection = $coll;
        $this->encoder = $enc;
        $this->productUrlPrefixModel = $productUrlPrefixM;
        $this->invalidValueArr = array("0", "None", "");
    }

    public function buildCsvs($storeId)
    {
        $GLOBALS['argvStoreId'] = $storeId;
        $listing = $this->getAllProductList();
        
        $this->createCSV($listing);
        $this->createYahooCSV($listing);
        $this->createFBCSV($listing);
    }

    public function createReviews($storeId)
    {
        $GLOBALS['argvStoreId'] = $storeId;
        $this->getProductsReviews('full');
    }

    public function updateReviews($storeId)
    {
        $GLOBALS['argvStoreId'] = $storeId;
        $this->getProductsReviews('inc');
    }

    public function checkFormat($text)
    {
        // TODO there is no recode_string function in php 7.4.x.
        // https://www.php.net/manual/en/function.iconv.php
        return $this->encoder->fixALL($text);
        return recode_string("us..flat", $this->encoder->fixALL($text));
    }
    
    public function minDisplay($arrayName)
    {
        $udisplay = '';
        if (!empty($arrayName)) {
            $udisplay = min(array_unique($arrayName));
        }
        
        return $udisplay;
    }
    
    public function uniqueDisplay($arrayName, $delimter = ' / ')
    {
        $udisplay = '';
        foreach (array_unique($arrayName) as $Name) {
            $udisplay .= $Name.$delimter;
        }
        $udisplay = substr($udisplay, 0, -(strlen($delimter)));
        if (strlen($udisplay) == 0) {
            $udisplay = "May Vary";
        }
        return $udisplay;
    }
    
    public function fileheader()
    {
        // Header
        return array(
            "setAdwordsGrouping",
            "setAgeGroup",
            "setBrand",
            "setCondition",
            "setDescription",
            "setGender",
            "setImageLink",
            "setProductLink",
            "setPrice",
            "setMpn",
            "setShippingWeight",
            "setColor",
            "setMaterial",
            "addSize",
            "setTitle",
            "setGoogleProductCategory",
            "setAtomId",
            "setAvailability",
            "setItemGroupId",
            "setProductType",
            "setSKU",
            "setSalePrice",
            "sale_price_effective_date",
            "Recommendable",
            "custom label 1",
            "custom label 2",
            "custom label 3"
        );
    }
    
    public function fileheaderYahoo()
    {
        // Header
        return array(
            "bingads_grouping",
            "AgeGroup",
            "Brand",
            "Condition",
            "Description",
            "Gender",
            "ImageURL",
            "ProductURL",
            "Price",
            "MPN",
            "ShippingWeight",
            "Color",
            "Material",
            "addSize",
            "Title",
            "MerchantCategory",
            "MPID",
            "Availability",
            "GroupId",
            "ProductType",
            "SKU",
            "pricewithdiscount",
            "sale_price_effective_date",
            "custom_label_0",
            "custom_label_1",
            "custom_label_2",
            "custom_label_3"
        );
    }
    
    public function fileheaderFB()
    {
        // Header
        return array(
            "id",
            "availability",
            "condition",
            "description",
            "image_link",
            "link",
            "title",
            "price",
            "mpn",
            "sale_price",
            "sale_price_effective_date"
        );
    }
    
    public function getAllProductList()
    {
        // Build Profile
        //     $storeId = Mage::app()->getStore($GLOBALS['argvStoreId']);
        //     Mage::app()->setCurrentStore($storeId);
        
        $this->storeManager->setCurrentStore($GLOBALS['argvStoreId']);
        
        //  $products = $this->productCollectionFactory->create()->setFlag('has_stock_status_filter', false)->load();FF
        $products = $this->productCollectionFactory->create();
        // https://github.com/magento/magento2/issues/15187
        /*
        $products->joinField(
            'store_id',
            'catalog_category_product_index',
            'store_id',
            'product_id=entity_id',
            '{{table}}.store_id = ' . $GLOBALS['argvStoreId'],
            'left'
        );
        $products->getSelect()->distinct(true);
        $products->joinAttribute(
            'visibility',
            'catalog_product/visibility',
            'entity_id',
            null,
            'inner',
            1
        );
        */
        $products->addStoreFilter($GLOBALS['argvStoreId']);
        $products->addFieldToFilter('visibility', array('4'));
        $products->addAttributeToFilter(
            'status',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        
        $products->load();
        
        $productArr = array();
        
        foreach ($products as $product) {
            try {
                $stockItem = $this->stockItemModel->get($product->getId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            //    continue;
            }
    //        echo $stockItem->getIsInStock() . '    ';
            if ($stockItem->getIsInStock() !== 1) {
           //     continue;
            }
            $productArr[] = $product;
        }
        
        $product_count = count($productArr);
        
        echo 'the product count is ' . $product_count . '<br />';
        foreach ($productArr as $product) {
            $prebuilts[] = $this->displayProd($product);
        }
        
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    public function getRecentProductList()
    {
        $date = date('Y-m-d', strtotime("now - 1 day"));
        // Build Profile
        $this->storeManager->setCurrentStore($GLOBALS['argvStoreId']);
        
        $products = $this->productCollectionFactory->create()->setFlag('has_stock_status_filter', false)->load();
        $products->addAttributeToSelect('*');
        $products->addAttributeToFilter('updated_at', array('gteq' => $date));
        $products->joinField(
            'store_id',
            'catalog_category_product_index',
            'store_id',
            'product_id=entity_id',
            '{{table}}.store_id = '. $GLOBALS['argvStoreId'],
            'left'
        );
        $products->getSelect()->distinct(true);
        $products->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner', 1);
        $products->addFieldToFilter('visibility', array('4'));
        
        $productArr = array();
        
        foreach ($products as $product) {
            try {
                $stockItem = $this->stockItemModel->get($product->getId());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                continue;
            }
            if ($stockItem->getIsInStock() == 1) {
                continue;
            }
            $productArr[] = $product;
        }
        
        $product_count = count($productArr);
        foreach ($productArr as $product) {
            $prebuilts[] = $this->displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    public function getOneProduct($pid)
    {
        // Build Profile
        $products = $this->productModel->create()->load($pid);
        $product_count = count($products);
        print_r($products);
        foreach ($products as $product) {
            $prebuilts[] = $this->displayProd($product);
        }
        return array('count' => $product_count, 'list' => $prebuilts);
    }
    
    public function getProdImage($product, $pid, $metal)
    {
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
		        mg.value_id = ' . $pid . '
		    and
		        mgv.store_id IN (0,1)
		    and
		        mgv.label LIKE "%' . $metal . '%"
		    and
		        mgv.label LIKE "%Default%"
		    order by
		        store_id,
		        value_id,
		        position
		    limit 1;';
        
        $fcDbQueryResults = $fcDbRead->fetchAll($fcDbQuery);
        
        if (isset($fcDbQueryResults[0]) && strlen($fcDbQueryResults[0]['value']) > 0) {
            //    $image = $product->getMediaConfig()->getMediaUrl($fcDbQueryResults[0]['value']);
            $image = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product' . $fcDbQueryResults[0]['value'];
        } else {
            //    $image = $product->getMediaConfig()->getMediaUrl($product->getData('image'));
            $image = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product' . $product->getImage();
        }
        return $image;
    }
    
    
    public function displayProd($productModel)
    {
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
        
        $url = $this->productUrlPrefixModel->getUrl($product);
        
        $main_image = $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'catalog/product' . $product->getImage();
        $sale_start_uf = new \DateTime($product->getSpecialFromDate());
        $sale_start = $sale_start_uf->format(\DateTime::ATOM);
        $sale_end_uf = new \DateTime($product->getSpecialToDate());
        $sale_end = $sale_end_uf->format(\DateTime::ATOM);
        $description = preg_replace('/\s+/S', " ", $this->checkFormat(strip_tags($product->getDescription())));
        $title = $name;
        $Category = "Apparel & Accessories > Jewelry";
        $ProductType = $Category;
        $high_margin = '';
        $bestseller = '';
        if (preg_match('/No/', $product->getAttributeText("high_margin"))) {
            $high_margin = "High Margin";
        }
        if (preg_match('/No/', $product->getAttributeText("bestseller"))) {
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
        switch ($attributeSetName) {
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
            switch ($option->getTitle()) {
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
        
        // Exception #0 (Exception): Warning: count(): Parameter must be an array or an object that implements Countable
        // Returns a string. Not countable.
        if (strlen($this->uniqueDisplay($Sizes)) > 0) {
            $size_display = "Varies";
        }
        $title = preg_replace('~[[:cntrl:]]~', '', preg_replace('/(\s)+/', ' ', preg_replace('/s$/', '', $title)));
        
        $Cuts = array();
        $Prices = array();
        $Colors = array();
        $Metals = array();
        $Gemstones = array();
        $SalePrices = array();
        $childs = array();
        switch ($product->getTypeId()) {
            case 'configurable':
                $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
                
                $childAttributeCodes = array();
                foreach ($configurableOptions as $configurableOpt) {
                    if (!empty($configurableOpt)) {
                        $childAttributeCodes[] = $configurableOpt[0]['attribute_code'];
                    }
                }
                
                $itemGroupId = $sku;
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childProducts as $simpleModel) {
                    // Future reference displayProd($simpleModel); will generate the simple
                    $_product = $this->productModel->create()->setStoreId(
                        $GLOBALS['argvStoreId']
                    )->load($simpleModel->getId());
                    if ($_product->getStatus() == 1) {
                        $SPrice = $_product->getPrice();
                        $sale_start_uf = new \DateTime($_product->getSpecialFromDate());
                        $ssale_start = $sale_start_uf->format(\DateTime::ATOM);
                        $sale_end_uf = new \DateTime($_product->getSpecialToDate());
                        $ssale_end = $sale_end_uf->format(\DateTime::ATOM);
                        $today = date('Y-m-dT00:00:00+00:00');
                        
                        if (($today >= $ssale_start)
                            && ($today < $ssale_end)
                            && ($_product->getPrice() != $_product->getFinalPrice())
                        ) {
                            $SSalePrice = $_product->getFinalPrice();
                            $onsale = "On Sale";
                        } else {
                            $SSalePrice = "";
                            $onsale = "";
                        }
                        $SCut = ($_product->getAttributeText('cut_type') == '')
                            ? "None" : $_product->getAttributeText('cut_type');
                        $SColor = ($_product->getAttributeText('color') == '')
                            ? "None" : $_product->getAttributeText('color');
                        $SMetal = ($_product->getAttributeText('metal_type') == '')
                            ? "None" : $_product->getAttributeText('metal_type');
                        $SGemstone = ($_product->getAttributeText('gemstone') == '')
                            ? "None" : $_product->getAttributeText('gemstone');
                        
                        $image = $this->getProdImage($product, $product->getId(), $SMetal);
                        
                        $Cuts[] = $SCut;
                        $Prices[] = $SPrice;
                        $Colors[] = $SColor;
                        $Metals[] = $SMetal;
                        $Gemstones[] = $SGemstone;
                        $SalePrices[] = $SSalePrice;
                        $simpleTitleMods = $SGemstone." ".$SCut." ".$SColor." ".$SMetal;
                        $simpleTitle = preg_replace(
                            '~[[:cntrl:]]~',
                            '',
                            preg_replace(
                                '/(\s)+/',
                                ' ',
                                preg_replace(
                                    '/s$/',
                                    '',
                                    preg_replace(
                                        '/ None/',
                                        '',
                                        $title . " " . $simpleTitleMods
                                    )
                                )
                            )
                        );

                        
                        $childs[] = array(
                            $attributeSetName,
                            "Adult",
                            $this->getWebsiteName(),
                            "New",
                            $description,
                            $Gender,
                            $image,
                            $url .  $this->buildConfigurableUrlString(
                                $_product,
                                $childAttributeCodes
                            ),
                            $SPrice,
                            $_product->getSku(),
                            "1",
                            $SColor,
                            $SMetal,
                            $size_display,
                            $simpleTitle,
                            $Category,
                            $_product->getId(),
                            "In Stock",
                            $itemGroupId,
                            $ProductType,
                            $sku,
                            $SSalePrice,
                            $ssale_start,
                            $ssale_end,
                            $recom,
                            $onsale,
                            $bestseller,
                            $high_margin
                        );
                    }
                }
                $price = $this->minDisplay($Prices);
                $saleprice = $this->minDisplay($SalePrices);
                break;
            case 'simple':
                $itemGroupId = false;
                $price = $product->getPrice();
                $sale_start_uf = new \DateTime($product->getSpecialFromDate());
                $ssale_start = $sale_start_uf->format(\DateTime::ATOM);
                $sale_end_uf = new \DateTime($product->getSpecialToDate());
                $ssale_end = $sale_end_uf->format(\DateTime::ATOM);
                $today = date('Y-m-dT00:00:00+00:00');
                if (($today >= $ssale_start)
                    && ($today < $ssale_end)
                    && ($product->getPrice() != $product->getFinalPrice())
                ) {
                    $saleprice = $product->getFinalPrice();
                    $onsale = "On Sale";
                } else {
                    $saleprice = "";
                    $onsale = "";
                }
                $Cuts[] = $product->getAttributeText('cut_type');
                $Colors[] = $product->getAttributeText('color');
                $Metals[] = $product->getAttributeText('metal_type');
                $image = $this->getProdImage($product, $product->getId(), $product->getAttributeText('metal_type'));
                $Gemstones[] = $product->getAttributeText('gemstone');
                $simpleTitleMods = $Gemstones[0] . " " . $Cuts[0] . " " . $Colors[0] . " " . $Metals[0];
                $title = preg_replace(
                    '~[[:cntrl:]]~',
                    '',
                    preg_replace(
                        '/(\s)+/',
                        ' ',
                        preg_replace(
                            '/s$/',
                            '',
                            preg_replace(
                                '/ None/',
                                '',
                                $title . " " . $simpleTitleMods
                            )
                        )
                    )
                );
                break;
            default:
                break;
        }
        
        $cuts_display = "";
        if (strlen($this->uniqueDisplay($Cuts)) > 0) {
            $cuts_display = "Varies";
        }
        $color_display = "";
        if (strlen($this->uniqueDisplay($Colors)) > 0) {
            $color_display = "Varies";
        }
        $metal_display = "";
        if (strlen($this->uniqueDisplay($Metals)) > 0) {
            $metal_display = "Varies";
        }
        $gems_display = "";
        if (strlen($this->uniqueDisplay($Gemstones)) > 0) {
            $gems_display = "Varies";
        }
        
        // Checks
        if ((strlen($description) > 2000) || (strlen($description) == 0)) {
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
            return array(
                'Result' => array(
                    $attributeSetName,
                    "Adult",
                    $this->getWebsiteName(),
                    "New",
                    $description,
                    $Gender,
                    $main_image,
                    $url,
                    $price,
                    $sku,
                    "1",
                    $color_display,
                    $metal_display,
                    $size_display,
                    $title,
                    $Category,
                    $pid,
                    "In Stock",
                    $itemGroupId,
                    $ProductType,
                    $sku,
                    $saleprice,
                    $sale_start,
                    $sale_end,
                    $recom,
                    $onsale,
                    $bestseller,
                    $high_margin
                ),
                'Children' => $childs
            );
        }
    }
    
    protected function buildConfigurableUrlString(
        $product,
        $childAttributeCodes
    ) {
        $ret = "?";
        $count = 0;
        
        foreach ($childAttributeCodes as $childAttributeCode) {
            $attributeText = $product->getAttributeText($childAttributeCode); 
            if ($this->isValidConfigurableAttributeValue($attributeText)) {
                if ($count != 0) {
                    $ret.= "&";
                }
                $count++;
                $ret .= $this->stripUrlString($childAttributeCode) . "=" . $this->stripUrlString($attributeText);
            }
        }
        return strtolower($ret);
    }
    
    protected function isValidConfigurableAttributeValue($val)
    {
        if (in_array(trim($val), $this->invalidValueArr)) {
            return false;
        }
        return true;
    }
    
    protected function stripUrlString($str)
    {
        $str = str_replace(".", "-", $str);
        $str = str_replace("  ", " ", $str);
        $str = str_replace(" ", "-", $str);
        $str = str_replace(",", "", $str);
        $str = str_replace("\\", "-", $str);
        $str = str_replace("/", "-", $str);
        $str = str_replace("'", "", $str);
        $str = str_replace("_", "-", $str);
        return $str;
    }
    
    public function cleanFeed()
    {
        $client = new ProductsFeed($GLOBALS['argvStoreId']);
        $productFeed = $client->listProducts();
        foreach ($productFeed as $product) {
            $product_exists = $this->productModel->create()
            ->setStoreId($GLOBALS['argvStoreId'])
            ->load($product);
            if (!(is_numeric($product)) && (!(preg_match('/online:en:US:/', $product)))) {
                print $product.": needs to die\n";
                #$client->deleteProduct($product);
            } elseif (!($this->productModel->create()->setStoreId($GLOBALS['argvStoreId'])->load($product))) {
                print $product.": No Longer Exists\n";
                $client->deleteProduct($product);
            } elseif ($product_exists->getStatus() != 1) {
                print $product.": ".$product_exists->getSku()." S: disabled\n";
                $client->deleteProduct($product);
            }
        }
    }
    
    public function doCheckFeed($listing)
    {
        $client = new ProductsFeed($GLOBALS['argvStoreId']);
        foreach ($listing['list'] as $disProd) {
            if (!empty($disProd)) {
                $feedproduct = $client->createProduct($disProd['Result']);
                try {
                    $result = $client->getProduct($feedproduct->getOfferId());
                } catch (Exception $e) {
                    if ($product->getId() > 0) {
                        try {
                            $feed = $client->insertProduct($feedproduct);
                        } catch (Exception $e) {
                            print "Missing Product - Id: ".$product->getId()." Sku: ".$product->getSku()."\n";
                            print_r($product);
                        }
                    }
                }
            }
        }
    }
    
    public function doFeed($listing)
    {
        $client = new ProductsFeed($GLOBALS['argvStoreId']);
        print "Building Feed. START: ".date(DATE_ATOM)."\n";
        $feed_insert_count = 0;
        foreach ($listing['list'] as $disProd) {
            $feedproduct = "";
            $shopproduct = "";
            if (!empty($disProd)) {
                print $disProd['result'][16] .
                " " . $feed_insert_count . " " .
                $disProd['Result'][9] . " " .
                $disProd['Result'][22] . " " .
                $disProd['Result'][23]."\n";
                $feedproduct = $client->createAdsProduct($disProd['Result']);
                if (is_numeric($feedproduct->getOfferId()) && ($feedproduct->getOfferId() > 0)) {
                    $feed[$feedproduct->getOfferId()] = $feedproduct;
                    $feed_insert_count += 1;
                    if ($feed_insert_count >= 100) {
                        $feed_insert_count = 0;
                        $client->insertProductBatch($feed);
                        $feed = '';
                    }
                    $no_kids = true;
                    foreach ($disProd['Children'] as $childProduct) {
                        $feedproduct = $client->createAdsProduct($childProduct);
                        if (is_numeric($feedproduct->getOfferId()) && ($feedproduct->getOfferId() > 0)) {
                            $feed[$feedproduct->getOfferId()] = $feedproduct;
                            $feed_insert_count += 1;
                            if ($feed_insert_count >= 100) {
                                $feed_insert_count = 0;
                                $client->insertProductBatch($feed);
                                $feed = '';
                            }
                        }
                        $shopproduct = $client->createShopProduct($childProduct);
                        if (is_numeric($shopproduct->getOfferId()) && ($shopproduct->getOfferId() > 0)) {
                            $feed[$shopproduct->getOfferId()] = $shopproduct;
                            $feed_insert_count += 1;
                            if ($feed_insert_count >= 100) {
                                $feed_insert_count = 0;
                                $client->insertProductBatch($feed);
                                $feed = '';
                            }
                        }
                        $no_kids = false;
                    }
                    if ($no_kids) {
                        $shopproduct = $client->createShopProduct($disProd['Result']);
                        if (is_numeric($shopproduct->getOfferId()) && ($shopproduct->getOfferId() > 0)) {
                            $feed[$shopproduct->getOfferId()] = $shopproduct;
                            $feed_insert_count += 1;
                            if ($feed_insert_count >= 100) {
                                $feed_insert_count = 0;
                                $client->insertProductBatch($feed);
                                $feed = '';
                            }
                        }
                    }
                }
            }
        }
        print "END: ".date(DATE_ATOM)." Total Magento Parent Items: ". $listing['count']."\n";
        print "###### Feed Complete ######\n";
    }
    
    public function getAverageRating(Review $review)
    {
        $avg = 0;
        if (count($review->getRatingVotes())) {
            $ratings = array();
            foreach ($review->getRatingVotes() as $rating) {
                $ratings[] = $rating->getPercent();
            }
            $avg = array_sum($ratings)/count($ratings);
        }
        return $avg;
    }
    
    public function reviewsHeader()
    {
        $reviewHeader = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $reviewHeader .= '<feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning"'
        . 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
        . 'xsi:noNamespaceSchemaLocation="'
        . 'http://www.google.com/shopping/reviews/schema/product/2.2/product_reviews.xsd">'."\n";
        $reviewHeader .= '<aggregator>'."\n";
        $reviewHeader .= '		<name>'. $this->getWebsiteName() . ' Reviews</name>'."\n";
        $reviewHeader .= '</aggregator>'."\n";
        $reviewHeader .= '<publisher>'."\n";
        $reviewHeader .= '        <name>'. $this->getWebsiteName().'</name>'."\n";
        $reviewHeader .= '</publisher>'."\n";
        return $reviewHeader;
    }
    
    public function updateAccount()
    {
        $client = new ProductsFeed($GLOBALS['argvStoreId']);
        $account = $client->updateMerchant();
        return $account;
    }
    
    public function getAccountInfo()
    {
        $client = new ProductsFeed($GLOBALS['argvStoreId']);
        $account = $client->getMerchantAccount();
        print "Name: ".$account->name." ID: ".$account->id."\n";
        print "Url: ".$account->websiteUrl."\n";
        print "Reviews: ".$account->reviewsUrl."\n";
        print "Adwords: ".$account->adwordsLinks[0]['adwordsId']." Status: ".$account->adwordsLinks[0]['status']."\n";
        foreach ($account->users as $user) {
            $admin = ($user->admin == 1) ? "Yes" : "No";
            print "User: ".$user->emailAddress." Admin: ".$admin."\n";
        }
    }
    
    public function getProductsReviews($type)
    {
        $varPathExport = $_SERVER['HOME'].'magento/var/export/reviews/incremental/'. $GLOBALS['argvStoreId']. '/';
        if (!file_exists($varPathExport)) {
            mkdir($varPathExport, 0777, true);
        }
        if ($type == 'inc') {
            $date = date('Y-m-d', (time() - 6048000));
            $datestamp = date('Y_m_d', time());
            $output = fopen($varPathExport . $datestamp.'_reviews.xml', 'w+')  or die("Unable to open file!");
            $collection = $this->reviewCollection->create()
            ->addFieldToSelect('*')
            ->addStoreFilter($GLOBALS['argvStoreId'])
            ->addFieldToFilter('created_at', array('gteq' => $date))
            ->addStatusFilter($this->reviewModel::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        } else {
            $output = fopen($varPathExport . 'reviews.xml', 'w+')  or die("Unable to open file!");
            $collection = $this->reviewCollection->create()
            ->addFieldToSelect('*')
            ->addStoreFilter($GLOBALS['argvStoreId'])
            ->addStatusFilter($this->reviewModel::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();
        }
        $collection->getSelect();
        print "Building Reviews...\n";
        fputs($output, $this->reviewsHeader());
        fputs($output, "\t<reviews>\n");
        foreach ($collection->getItems() as $review) {
            $product = $this->productModel->create()->setStoreId(
                $GLOBALS['argvStoreId']
            )->load($review->getEntityPkValue());
            $timestamp = date(DATE_ATOM, strtotime($review->getCreatedAt()));
            
            $url = $this->productUrlPrefixModel->getUrl($product);
            
            $content = preg_replace(
                "/(\. \. \.)/",
                '',
                preg_replace(
                    "/(\.)\\1+/",
                    '',
                    str_replace(
                        '&',
                        'and',
                        strip_tags(
                            html_entity_decode(
                                $this->checkFormat(
                                    $review->getDetail()
                                )
                            )
                        )
                    )
                )
            );
            $title = preg_replace(
                "/(\. \. \.)/",
                '',
                preg_replace(
                    "/(\.)\\1+/",
                    '',
                    str_replace(
                        '&',
                        'and',
                        strip_tags(
                            html_entity_decode(
                                $this->checkFormat(
                                    $review->getTitle()
                                )
                            )
                        )
                    )
                )
            );
            $name = preg_replace(
                "/(\. \. \.)/",
                '',
                preg_replace(
                    "/(\.)\\1+/",
                    '',
                    str_replace(
                        '&',
                        'and',
                        strip_tags(
                            html_entity_decode(
                                $this->checkFormat(
                                    $review->getNickname()
                                )
                            )
                        )
                    )
                )
            );
            $product_name = preg_replace(
                "/(\.)\\1+/",
                '.',
                str_replace(
                    '&',
                    'and',
                    strip_tags(
                        html_entity_decode(
                            $this->checkFormat(
                                $product->getName()
                            )
                        )
                    )
                )
            );
            if (strlen($content) > 0) {
                $prodXml = "\t\t<review>\n";
                $prodXml .= "\t\t\t<review_id>".$review->getReviewId()."</review_id>\n";
                $prodXml .= "\t\t\t<reviewer>\n";
                $prodXml .= "\t\t\t\t<name>".$name."</name>\n";
                $prodXml .= "\t\t\t</reviewer>\n";
                $prodXml .= "\t\t\t<review_timestamp>".$timestamp."</review_timestamp>\n";
                $prodXml .= "\t\t\t<title>".$title."</title>\n";
                $prodXml .= "\t\t\t<content>".$content."</content>\n";
                $prodXml .= "\t\t\t<review_url type='group'>".$url."</review_url>\n";
                $prodXml .= "\t\t\t<ratings>\n";
                $prodXml .= "\t\t\t\t<overall min='0' max='100'>".$this->getAverageRating($review)."</overall>\n";
                $prodXml .= "\t\t\t</ratings>\n";
                $prodXml .= "\t\t\t<products>\n";
                $prodXml .= "\t\t\t\t<product>\n";
                $prodXml .= "\t\t\t\t\t<product_ids>\n";
                $prodXml .= "\t\t\t\t\t\t<skus>\n";
                $prodXml .= "\t\t\t\t\t\t\t<sku>".$product->getSku()."</sku>\n";
                $prodXml .= "\t\t\t\t\t\t</skus>\n";
                $prodXml .= "\t\t\t\t\t\t<mpns>\n";
                $prodXml .= "\t\t\t\t\t\t\t<mpn>".$product->getSku()."</mpn>\n";
                $prodXml .= "\t\t\t\t\t\t</mpns>\n";
                $prodXml .= "\t\t\t\t\t\t<brands>\n";
                $prodXml .= "\t\t\t\t\t\t\t<brand>".$this->getWebsiteName()."</brand>\n";
                $prodXml .= "\t\t\t\t\t\t</brands>\n";
                $prodXml .= "\t\t\t\t\t</product_ids>\n";
                $prodXml .= "\t\t\t\t\t<product_name>".$product_name."</product_name>\n";
                $prodXml .= "\t\t\t\t\t<product_url>".$url."</product_url>\n";
                $prodXml .= "\t\t\t\t</product>\n";
                $prodXml .= "\t\t\t</products>\n";
                $prodXml .= "\t\t\t<is_spam>false</is_spam>\n";
                $prodXml .= "\t\t</review>\n";
                fputs($output, $prodXml);
            }
        }
        fputs($output, "\t</reviews>\n</feed>\n");
        print "Complete!\n";
    }
    
    public function createCSV($listing)
    {
        $varPathExport = $_SERVER['HOME'].'magento/var/export/';
        if (!file_exists($varPathExport)) {
            mkdir($varPathExport, 0777, true);
        }
        
        $output = fopen($varPathExport. '/base_feed_store_'. $GLOBALS['argvStoreId'] .'.csv', 'w+');
        fputcsv($output, $this->fileheader(), '|', '"');
        foreach ($listing['list'] as $result) {
            
            if (!empty($result)) {
                
               $result['Result'][0] = str_replace("Migration_", "", $result['Result'][0]);
                
               if ($result['Result'][0] == "Loose Diamonds") {
                   continue;
               }
                
                if ($result['Result'][22] != $result['Result'][23] && $result['Result'][21] > 0) {
                    $result['Result'][22] .= "/". $result['Result'][23];
                } else {
                    $result['Result'][22] = '';
                }
                unset($result['Result'][23]);
                fputcsv($output, $result['Result'], '|', '"');
                foreach ($result['Children'] as $childProduct) {
                    
                    $childProduct[0] = str_replace("Migration_", "", $childProduct[0]);
                    
                    if ($childProduct[0] == "Loose Diamonds") {
                        continue;
                    }
                    
                    if ($childProduct[22] != $childProduct[23] && $childProduct[21] > 0) {
                        $childProduct[22] .= "/". $childProduct[23];
                    } else {
                        $childProduct[22] = '';
                    }
                    unset($childProduct[23]);
                    fputcsv($output, $childProduct, '|', '"');
                }
            }
        }
    }
    
    public function createYahooCSV($listing)
    {
        $varPathExport = $_SERVER['HOME'].'magento/var/export/';
        if (!file_exists($varPathExport)) {
            mkdir($varPathExport, 0777, true);
        }
        
        $output = fopen($varPathExport.'base_feed_yahoo_store_'. $GLOBALS['argvStoreId'] .'.txt', 'w+');
        fputcsv($output, $this->fileheaderYahoo(), "\t", '"');
        foreach ($listing['list'] as $result) {
            if (!empty($result)) {
                if ($result['Result'][22] != $result['Result'][23] && $result['Result'][21] > 0) {
                    $result['Result'][22] .= "/". $result['Result'][23];
                } else {
                    $result['Result'][22] = '';
                }
                unset($result['Result'][23]);
                fputcsv($output, $result['Result'], "\t", '"');
                foreach ($result['Children'] as $childProduct) {
                    if ($childProduct[22] != $childProduct[23] && $childProduct[21] > 0) {
                        $childProduct[22] .= "/". $childProduct[23];
                    } else {
                        $childProduct[22] = '';
                    }
                    unset($childProduct[23]);
                    fputcsv($output, $childProduct, "\t", '"');
                }
            }
        }
    }
    
    public function createFBCSV($listing)
    {
        $varPathExport = $_SERVER['HOME'] . 'magento/var/export/';
        
        if (!file_exists($varPathExport)) {
            mkdir($varPathExport, 0777, true);
        }
        
        $childCount = 0;
        $parentCount = 0;
        
        $output = fopen($varPathExport.'base_feed_fb_store_'. $GLOBALS['argvStoreId'] .'.txt', 'w+');
        fputcsv($output, $this->fileheaderFB(), "\t", '"');
        
        foreach ($listing['list'] as $result) {
            if (!empty($result)) {
            //    echo count($result['Children']) . '    ';
                
                if (count($result['Children']) > 0) {
                    foreach ($result['Children'] as $childProduct) {
                        if ($childProduct[16] != "") {
                            $childCount++;
                            $fb_line = array(
                                $childProduct[16],
                                "available for order",
                                "new",
                                $childProduct[4],
                                $childProduct[6],
                                $childProduct[7],
                                substr(
                                    $childProduct[14],
                                    0,
                                    100
                                ),
                                $childProduct[8],
                                $childProduct[9],
                                $childProduct[21],
                                $childProduct[22] . "/" . $childProduct[23]
                            );
                            fputcsv($output, $fb_line, "\t", '"');
                        }
                    }
                } else {
                    if (isset($result['Result'][16]) &&  trim($result['Result'][16]) != "") {
                        $parentCount++;
                        
                        $fb_line = array(
                            $result['Result'][16],
                            "available for order",
                            "new",
                            $result['Result'][4],
                            $result['Result'][6],
                            $result['Result'][7],
                            substr(
                                $result['Result'][14],
                                0,
                                100
                            ),
                            $result['Result'][8],
                            $result['Result'][9],
                            $result['Result'][21],
                            $result['Result'][22] . "/" . $result['Result'][23]
                        );
                        fputcsv($output, $fb_line, "\t", '"');
                    }
                }
            }
        }
        
        echo 'the child count is ' . $childCount . ' and the parent count is ' . $parentCount . '  ';
    }
    
    public function getWebsiteName()
    {
        return $this->storeManager->getStore($GLOBALS['argvStoreId'])->getWebsite()->getName();
    }
    
    public function usage()
    {
        print "build_feed.php <action> (StoreId) (ProdId)\n";
        print "status - Check feed statuses\n";
        print "single (ProdId) - Add single product to base feed\n";
        print "checksingle (ProdId) - Check single proiduct on base feed\n";
        print "all - Send all products to feed\n";
        print "check - Check Mag Prod Db against Feed, add/remove missing.\n";
        print "clean - Delete feed products no long in magento.\n";
        print "update - Send recently updated products to feed.\n";
        print "help - this menu.\n";
        exit;
    }
}
