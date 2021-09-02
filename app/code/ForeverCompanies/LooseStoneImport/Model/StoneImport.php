<?php
namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory as SoldProductCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InitException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Exception\TemporaryState\CouldNotSaveException as TemporaryStateCouldNotSaveException;
use Magento\Framework\Exception\AbstractAggregateException;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\InvalidArgumentException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Exception\SecurityViolationException;
use Magento\Framework\Exception\SerializationException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;

class StoneImport
{    
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;
    protected $mediaTmpDir;
    protected $file;
    protected $connection;
    
    protected $booleanMap;
    protected $csvHeaderMap;
    protected $clarityMap;
    protected $cutGradeMap;
    protected $colorMap;
    protected $shapeMap;
    protected $supplierMap;
    
    protected $shapePopMap;
    protected $shapeAlphaMap;
    protected $shippingStatusMap;
    
    protected $claritySortMap;
    protected $cutGradeSortMap;
    protected $colorSortMap;
    
    protected $csv;
    
    protected $fileName;
    protected $requiredFieldsArr;
    protected $soldProductCollectionFactory;
    protected $productRepo;
    
    protected $statusEnabled;
    protected $statusDisabled;
    
    protected $supplierStatuses;
    
    protected $scopeConfig;
    protected $storeScope;
    
    public function __construct(
        CollectionFactory $collectionFactory,
        Product $prod,
        ProductFactory $prodF,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo,
        Csv $cs,
        DirectoryList $directoryList,
        File $fil,
        SoldProductCollectionFactory $soldProductColl,
        ProductRepository $productR,
        ScopeConfigInterface $scopeC
        ) {
            $this->productCollectionFactory = $collectionFactory;
            $this->productModel = $prod;
            $this->resourceConnection = $resource;
            $this->attributeSetMod = $attributeSetRepo;
            $this->stockItemModel = $stockItemRepo;
            $this->csv = $cs;
            $this->productFactory = $prodF;
            $this->file = $fil;
            $this->soldProductCollectionFactory = $soldProductColl;
            $this->productRepo = $productR;
            
            $this->mediaTmpDir = $directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
            $this->file->checkAndCreateFolder($this->mediaTmpDir );
            $this->connection = $resource->getConnection();
            $this->scopeConfig = $scopeC;
            $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            
            $this->csvHeaderMap = array(
                "Product Name" => "name",
                "Certificate #" => "sku",
                "Lab" => "lab",
                "Weight" => "weight",
                "Length" => "length",
                "Width" => "width",
                "Depth (mm)" => "depth_mm",
                "Length to Width" => "length_to_width",
                "Depth %" => "depth_pct",
                "Measurements" => "measurements",
                "Table %" => "table_pct",
                "Polish" => "polish",
                "Symmetry" => "symmetry",
                "Girdle" => "girdle",
                "Culet" => "culet",
                "Fluorescence" => "fluor",
                "Country of Origin" => "origin",
                "As Grown" => "as_grown",
                "Born on Date" => "born_on_date",
                "Carbon Neutral" => "carbon_neutral",
                "Blockchain Verified" => "blockchain_verified",
                "Charitable Contribution" => "charitable_contribution",
                "CVD" => "cvd",
                "HPHT" => "hpht",
                "Patented" => "patented",
                "Custom" => "custom",
                "Color of Colored Diamonds" => "color_of_colored_diamonds",
                "Hue" => "hue",
                "Intensity" => "intensity",
                "Rapaport" => "rapaport",
                "% Off Rap" => "pct_off_rap",
                "MSRP" => "msrp",
                "Price" => "price",
                "Cost" => "cost",
                "Certificate URL" => "cert_url_key",
                "Image Link" => "diamond_img_url",
                "Video" => "video_url",
                "Online" => "online"
            );
            
            $this->booleanMap = array(
                "Yes" => "1",
                "yes" => "1",
                "No" => "1",
                "no" => "1"
            );
            
            $this->claritySortMap = array(
                "SI2" => "100",
                "SI1" => "200",
                "VS2" => "300",
                "VS1" => "400",
                "VVS2" => "500",
                "VVS1" => "600",
                "IF" => "2854",
                "FL" => "3564",
                "I1" => "2853",
                "I3" => "3480",
                // TODO: Remove. Adding to get through import.
                "G" => ""
            );
            
            $this->cutGradeSortMap = array(
                "Good" => "100",
                "Very Good" => "200",
                "Excellent" => "300",
                "Ideal" => "400",
                "Super Ideal" => "500",
                // TODO: Remove. Adding to get through import.
                "G" => ""
            );
            
            $this->colorSortMap = array(
                "J" => "100",
                "I" => "200",
                "H" => "300",
                "G" => "400",
                "F" => "500",
                "E" => "600",
                "D" => "700"
            );
            
            $this->shapePopMap = array(
                "Round" => "100",
                "round" => "100",
                "Princess" => "200",
                "princess" => "200",
                "Cushion" => "300",
                "cushion" => "300",
                "Oval" => "400",
                "oval" => "400",
                "Emerald" => "500",
                "emerald" => "500",
                "Pear" => "600",
                "pear" => "600",
                "Asscher" => "700",
                "asscher" => "700",
                "Radiant" => "800",
                "radiant" => "800",
                "Marquise" => "900",
                "marquise" => "900",
                "Heart" => "1000",
                "heart" => "1000"
            );
            
            $this->shapeAlphaMap = array(
                "Round" => "1000",
                "round" => "1000",
                "Princess" => "800",
                "princess" => "800",
                "Cushion" => "200",
                "cushion" => "200",
                "Oval" => "600",
                "oval" => "600",
                "Emerald" => "300",
                "emerald" => "300",
                "Pear" => "700",
                "pear" => "700",
                "Asscher" => "100",
                "asscher" => "100",
                "Radiant" => "900",
                "radiant" => "900",
                "Marquise" => "500",
                "marquise" => "500",
                "Heart" => "400",
                "heart" => "400"
            );
            
            $this->shippingStatusMap = array(
                "ZeroDay" => "0 Day",
                "Last Minute" => "0 Day",
                "Immediate" => "1 Day",
                "TwoDay" => "2 Day",
                "ThreeDay" => "3 Day",
                "FourDay" => "4 Day",
                "WarrantyFour" => "4 Day",
                "Rapid" => "5 Day",
                "SixDay" => "6 Day",
                "SevenDay" => "7 Day",
                "Warranty" => "7 Day",
                "Standard" => "8 Day",
                "TenDay" => "10 Day",
                "Extended" => "12 Day",
                "FourteenDay" => "14 Day",
                "FifteenDay" => "15 Day",
                "Backordered" => "17 Day",
                "TwentyDay" => "20 Day",
                "TwentyOneDay" => "20 Day",
                // fifty day isn't supported by any shipping api (update to 20)
                "FiftyDay" => "20 Day"
            );
            
            $this->requiredFieldsArr = array(
                // TODO: Yeah this is an interesting one. Encoding, maybe? Try it out.
                // "Product Name", 
                "Supplier",
                "Certificate #",
                "Shape Name",
                "Lab",
                "Weight",
                "Color",
                "Clarity",
                "Cut Grade",
                "Length",
                "Width"
            );
            
            $this->clarityMap = array(
                "FL" => "3564",
                "I1" => "2853",
                "I3" => "3480",
                "IF" => "2854",
                "SI1" => "2857",
                "SI2" => "2858",
                "VS1" => "2859",
                "VS2" => "2861",
                "VVS1" => "2862",
                "VVS2" => "2863"
            );
            
            $this->cutGradeMap = array(
                "excellent" => "2876",
                "ex" => "2876",
                "not specified" => "3076",
                "ideal" => "2877",
                "super ideal" => "3602",
                "very good" => "2878",
                "good" => "2879",
                // TODO: Create this attribute option and place its value here.
                "fair" => "",
                // TODO: Remove. Adding to get through import.
                "g" => "",
                "-" => "",
                "none" => ""
            );
            
            $this->colorMap = array(
                "Black" => "136",
                "Black Multi" => "138",
                "Black Pearl" => "1727",
                "Black White" => "137",
                "Blue Quartz" => "2127",
                "Blue Topaz" => "139",
                "C" => "2864",
                "Canary" => "140",
                "Canary Sapphire" => "2297",
                "Canary White" => "1827",
                "Champagne" => "141",
                "Champagne Chocolate" => "1754",
                "Champagne Multi" => "143",
                "Champagne White" => "142",
                "Charcoal Titanium" => "2288",
                "Chocolate" => "144",
                "Chocolate Multi" => "146",
                "Chocolate White" => "145",
                "CocoBollo Damascus" => "2287",
                "CocoBollo Titanium" => "2290",
                "Cross Satin" => "2283",
                "Cross Satin Black" => "2285",
                "Cross Satin Silver" => "2284",
                "Emerald" => "147",
                "Emerald Multi" => "149",
                "Emerald White" => "148",
                "Fiji Orangewood Black Zirconium" => "2282",
                "Glacial Ice" => "150",
                "Glacial Ice Sapphire" => "1803",
                "Glacial Ice White" => "151",
                "Gold" => "16",
                "Hammer" => "2289",
                "I3" => "3479",
                "Meteorite" => "2296",
                "Multi Color" => "152",
                "Multi Topaz" => "153",
                "New Canary Multi" => "155",
                "New Canary White" => "154",
                "None" => "156",
                "Pink Topaz" => "157",
                "Red Topaz" => "158",
                "Rose" => "159",
                "Rose Multi" => "161",
                "Rose Ruby" => "1802",
                "Rose White" => "160",
                "Rosewood Titanium" => "2286",
                "Ruby" => "162",
                "Ruby Multi" => "164",
                "Ruby White" => "163",
                "Sapphire" => "165",
                "Sapphire Canary" => "2298",
                "Sapphire Multi" => "167",
                "Sapphire White" => "166",
                "Sea Green Chalcedony" => "2132",
                "Smokey Quartz" => "2126",
                "White" => "14",
                "White Black" => "169",
                "White Champagne" => "170",
                "White Chocolate" => "171",
                "White Emerald" => "172",
                "White Glacial Ice" => "173",
                "White Multi" => "178",
                "White New Canary" => "174",
                "White Pearl" => "1728",
                "White Rose" => "168",
                "White Ruby" => "2327",
                "White Sapphire" => "2329",
                "White Smokey Quartz" => "2477",
                "White Topaz" => "177",
                "Yellow Topaz" => "15",
                "D" => "2865",
                "E" => "2866",
                "F" => "2867",
                "G" => "2868",
                "H" => "2869",
                "I" => "2870",
                "J" => "2871",
                "K" => "2872",
                "L" => "2873",
                "M" => "2874",
                "N" => "2875",
            );
            
            $this->shapeMap = array(
                "Round" => "2842",
                "round" => "2842",
                "Princess" => "2843",
                "princess" => "2843",
                "Asscher" => "2844",
                "asscher" => "2844",
                "Cushion" => "2845",
                "cushion" => "2845",
                "Heart" => "2846",
                "heart" => "2846",
                "Oval" => "2847",
                "oval" => "2847",
                "Emerald" => "2848",
                "emerald" => "2848",
                "Radiant" => "2849",
                "radiant" => "2849",
                "Pear" => "2850",
                "pear" => "2850",
                "Marquise" => "2851",
                "marquise" => "2851",
                // TODO: Remove. Adding to get through import.
                "RB" => "",
                "EM" => ""
            );
            
            $this->supplierMap = array(
                "blumoon" => "1",
                "classic" => "2",
                "greenrocks" => "3",
                "internal" => "4",
                "labrilliante" => "5",
                "paradiam" => "6",
                "pdc" => "7",
                "stuller" => "8 ",
                "washington" => "9",
                "foundry" => "10",
                "diamondfoundry" => "10",
                "meylor" => "11",
                "ethereal" => "12",
                "smilingrocks" => "13",
                "unique" => "14",
                "qualitygold" => "15",
                "flawlessallure" => "16",
                "labs" => "17",
                "labsdiamond" => "17",
                "Fenix" => "18",
                "fenix" => "18",
                "brilliantdiamonds" => "19",
                "growndiamondcorpusa" => "20",
                "internationaldiamondjewelry" => "21",
                "ecogrown" => "26",
                "purestones" => "27",
                "proudest" => "28",
                "proudestlegendlimited" => "28",
                "dvjcorp" => "29",
                "dvjewelrycorporation" => "29",
                "indiandiamonds" => "31",
                "growndiamondcorp" => "32",
                "lush" => "33",
                "lushdiamonds" => "33",
                "altr" => "34",
                "ALTR" => "34",
                "Forever Grown" => "35",
                "internalaltr" => "36",
                "bhaktidiamond" => "37",
                "bhakti" => "37"
                // TODO: Create these attribute options and place their values here.
                // "bhakti" => ""
            );
            
            $this->fileName = $_SERVER['HOME'] . 'magento/var/import/diamond_importer.csv';
            
            $this->statusEnabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED; 
            $this->statusDisabled = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            
            $supplierData = $this->connection->fetchAll("SELECT `enabled`, `code` FROM `stones_supplier`");
            
            
            $this->supplierStatuses = array();
            foreach ($supplierData as $supplierD) {
                $this->supplierStatuses[strtolower($supplierD['code'])] = $supplierD['enabled'];
                
                if ($supplierD['code'] == "bhaktidiamond") {
                    $this->supplierStatuses["bhakti"] = $supplierD['enabled'];;
                }
                if ($supplierD['code'] == "diamondfoundry") {
                    $this->supplierStatuses["foundry"] = $supplierD['enabled'];;
                }
                elseif ($supplierD['code'] == "labs") {
                    $this->supplierStatuses["labsdiamond"] = $supplierD['enabled'];;
                }
                elseif ($supplierD['code'] == "Fenix") {
                    $this->supplierStatuses["fenix"] = $supplierD['enabled'];;
                }
                elseif ($supplierD['code'] == "proudestlegendlimited") {
                    $this->supplierStatuses["proudest"] = $supplierD['enabled'];;
                }
                elseif ($supplierD['code'] == "lushdiamonds") {
                    $this->supplierStatuses["lush"] = $supplierD['enabled'];;
                }
            }
            
    }
    
    function deleteUnsoldDiamonds()
    {
        $soldColl = $this->soldProductCollectionFactory->create()
        ->addAttributeToSelect('sku')
        ->addOrderedQty();
        
        $soldArr = array();
        foreach ($soldColl as $sold) {
            $soldArr[] = $sold->getData("order_items_sku");
        }
        
        $unsoldAndDisabledDiamondProductColl = $this->productCollectionFactory->create()
        ->addAttributeToFilter('product_type', '3569')
        ->addAttributeToFilter('status', $this->statusDisabled)
        ->addAttributeToFilter('sku', array('nin' => $soldArr))
        ->addAttributeToSelect('sku');
        
        unset($soldColl);
        unset($soldArr);
        
        foreach ($unsoldAndDisabledDiamondProductColl as $unsoldAndDisabledDiamond) {
            $this->productRepo->deleteById($unsoldAndDisabledDiamond->getSku());
        }
        
        return $this;
    }
    
    function run()
    {
        $this->_updateCsv();
        $csvArray = $this->_buildArray();
        
        $i = 0;
        foreach ($csvArray as $csvArr) {
            try {
            if (!$this->_checkForRequiredFields($csvArr)) {
                
                $product = new \Magento\Framework\DataObject();
                if (isset($csvArr['Certificate #'])) {
                    $product->setSku($csvArr['Certificate #']);
                }
                $this->_stoneLog($product, $csvArr, "error", "Required field invalid.");
                continue;
            }
            /*
            echo 'the supplier is ' . $csvArr['Supplier'] . '<br />';
            
            if ($i==25) {
                die;
            }
            $i++;
            */
            
            $productId = $this->productModel->getIdBySku($csvArr['Certificate #']);
            if ($productId) {
                $product = $this->productModel->load($productId); 
                
                // if product has been disabled assume it has been sold(or supplier was disabled, which will end up with product being deleted later)
                if ($product->getStatus() == $this->statusDisabled) {
                    unset($productId);
                    unset($product);
                    continue;
                }
              
                $success = $this->_applyCsvRowToProduct($product, $csvArr);
                
                if ($success) {
                    $this->_stoneLog($product, $csvArr, "update");
                }
                
            } else { // else new product
                
                $product = $this->productFactory->create();
                
                $imageFileName = $this->mediaTmpDir . DIRECTORY_SEPARATOR . basename($csvArr['Image Link']);
                
                $imagePathInfo = pathinfo($imageFileName);
                
                if (!isset($imagePathInfo['extension'])) {
                    if (isset($imagePathInfo['mime']) && $imagePathInfo['mime'] == 'image/jpeg') {
                        $imageFileName .= ".jpg";
                    } else {
                        $imageFileName .= ".jpg";
                    }
                }
                
                $imageResult = $this->file->read($csvArr['Image Link'], $imageFileName);

                if ($imageResult) {
                    try {
                        $product->addImageToMediaGallery(
                            $imageFileName,
                            ['image', 'small_image', 'thumbnail'],
                            false,
                            false
                        );
                    } catch(\Magento\Framework\Exception\LocalizedException $e) {
                        $this->_stoneLog($product, $csvArr, "error", "New Product " . $csvArr['Certificate #'] . " not created. Incorrect image extension");
                        continue;
                    }
                    
                } else {
                    $this->_stoneLog($product, $csvArr, "error", "New Product " . $csvArr['Certificate #'] . " not created. No image.");
                    continue;
                }
                
                $product->setName(reset($csvArr));
                $product->setTypeId('simple');
                $product->setAttributeSetId(31);
                $product->setSku($csvArr['Certificate #']);
                $product->setStatus($this->statusEnabled);
                
                $product->setVisibility(1);
                
                // From the admin, the reps can use a diamond on a 1215 or FA order.  On the frontend we do not display diamonds on FA.
                // need to assign visibility for each store somehow.
                $product->setWebsiteIds(array(2,3));
         
                $product->setStockData(
                    array(
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 1,
                        'min_sale_qty' => 1,
                        'max_sale_qty' => 1,
                        'is_in_stock' => 1,
                        'qty' => 1
                    )
                );
                
                $product->setTaxClassId(2);
             
                $success = $this->_applyCsvRowToProduct($product, $csvArr);
                
                if ($success) {
                    $this->_stoneLog($product, $csvArr, "add");
                    // 1215 storefront visibility.
                    $product->setStoreId(12)->setVisibility(4)->save();
                }
                
                unset($imageFileName);
                unset($imageResult);
            }
            
            unset($productId);
            unset($product);
            unset($csvArr);
            
            } catch(PluginAuthenticationException | ExpiredException | InitException | InputMismatchException | InvalidTransitionException | UserLockedException | TemporaryStateCouldNotSaveException
                | AbstractAggregateException | AlreadyExistsException | AuthenticationException | AuthorizationException | BulkException | ConfigurationMismatchException | CouldNotDeleteException
                | CouldNotSaveException | CronException | EmailNotConfirmedException | FileSystemException | InputException | IntegrationException | InvalidArgumentExceptionm | InvalidEmailOrPasswordException
                | LocalizedException | MailException | NoSuchEntityException | NotFoundException | PaymentException | RemoteServiceUnavailableException | RuntimeException | SecurityViolationException
                | SerializationException | SessionException | StateException | ValidatorException $e
            ) {
                $product = new \Magento\Framework\DataObject();
                if (isset($csvArr['Certificate #'])) {
                    $product->setSku($csvArr['Certificate #']);
                }
                $this->_stoneLog($product, $csvArr, "error", $csvArr['Certificate #'] . " not processed. " . $e->getMessage());
            }
            catch(Exception $e)
            {
                $product = new \Magento\Framework\DataObject();
                if (isset($csvArr['Certificate #'])) {
                    $product->setSku($csvArr['Certificate #']);
                }
                $this->_stoneLog($product, $csvArr, "error", $csvArr['Certificate #'] . " not processed. " . $e->getMessage());
            }
        } // end foreach ($csvArray as $csvArr) {
        
        $this->_cleanLogs();
    }
    
    protected function _applyCsvRowToProduct($product, $csvArr)
    {
        $product->setProductType('3569'); //diamond
        
        // These have been checked as required fields.
        $product->setColor($this->colorMap[$csvArr['Color']]);
        $product->setClarity($this->clarityMap[$csvArr['Clarity']]);
        $product->setCutGrade($this->cutGradeMap[strtolower($csvArr['Cut Grade'])]);
        $product->setShape($this->shapeMap[$csvArr['Shape Name']]);
        $product->setSupplier(strtolower($this->supplierMap[$csvArr['Supplier']]));
        
        if (isset($csvArr['Super Ideal'])) {
            $product->setSuperIdeal($csvArr['Super Ideal']);
        }
        
        
        if (isset($this->supplierStatuses[strtolower($csvArr['Supplier'])])) {
            $this->_handleStatus($csvArr['Supplier']);
        } else {
            $this->_stoneLog($product, $csvArr, "error", "Supplier does not exist - " . $csvArr['Supplier']);
            unset($product);
            unset($csvArr);
            return false;
        }
        
        // Sorting
        if (isset($this->claritySortMap[$csvArr['Clarity']])) {
            $product->setClaritySort($this->claritySortMap[$csvArr['Clarity']]);
        }
        if (isset($this->colorSortMap[$csvArr['Color']])) {
            $product->setColorSort($this->colorSortMap[$csvArr['Color']]);
        }
        if (isset($this->cutGradeSortMap[strtolower($csvArr['Cut Grade'])])) {
            $product->setCutGradeSort($this->cutGradeSortMap[strtolower($csvArr['Cut Grade'])]);
        }
        if (isset($this->shapePopMap[$csvArr['Shape Name']])) {
            $product->setShapePopSort($this->shapePopMap[$csvArr['Shape Name']]);
        }
        if (isset($this->shapeAlphaMap[$csvArr['Shape Name']])) {
            $product->setShapeAlphaSort($this->shapeAlphaMap[$csvArr['Shape Name']]);
        }
        
        //Delivery Date
        if (isset($csvArr['Delivery Date']) && trim($csvArr['Delivery Date']) != "") {
            $product->setShippingStatus($this->shippingStatusMap[$csvArr['Delivery Date']]);
        }
        
        // Blockchain Verified
        if (isset($csvArr['Blockchain Verified']) && trim($csvArr['Blockchain Verified']) != "") {
            $product->setBlockchainVerified($this->booleanMap[$csvArr]['Blockchain Verified']);
        }
        
        // Mapped
        foreach ($csvArr as $csvK => $csvV) {
            if (isset($this->csvHeaderMap[$csvK]) && trim($this->csvHeaderMap[$csvK]) != "") {
                $product->setData($this->csvHeaderMap[$csvK], $csvV);
            }
        }
        $product->save();
        return true;
    }
    
    protected function _buildArray()
    {
        $arr = array();
        $fields = array();
        $i = 0;
        
        if (file_exists($this->fileName)) {
            $csvData = $this->csv->getData($this->fileName);
            foreach ($csvData as $k => $val) {
                if ($k == 0) {
                    $fields = $val;
                    continue;
                }
                foreach ($val as $k=>$value) {
                    $arr[$i][$fields[$k]] = $value;
                }
                $i++;
            }
        }
        return $arr;
    }
    
    protected function _checkForRequiredFields($arr)
    {
        foreach ($this->requiredFieldsArr as $req) {
            if (!isset($arr[$req]) || trim($arr[$req]) == "" || $arr[$req] == "Nan") {
                return false;
            }
        }
        return true;
    }
    
    protected function _cleanLogs()
    {
        $query = "DELETE FROM stone_log
        WHERE log_date < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))";
        
        $this->connection->query($query);
    }

    protected function _getHash($csvArr)
    {
        return hash('sha1', json_encode($csvArr)); 
    }
    
    protected function _getMapForAttributevfdsfvsdsfdsdfsdsfwADS()
    {
        $attributeCode = 'supplier';
        $entityType = 'catalog_product';
        
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        
        $attributeInfo = $objectManager->get(\Magento\Eav\Model\Entity\Attribute::class)
        ->loadByCode($entityType, $attributeCode);
        
        $attributeId = $attributeInfo->getAttributeId();
        $attributeOptionAll = $objectManager->get(\Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection::class)
        ->setPositionOrder('asc')
        ->setAttributeFilter($attributeId)
        ->setStoreFilter()
        ->load();
        
        foreach ($attributeOptionAll->getData() as $attributeOption) {
            echo '"' . $attributeOption['default_value'] . '" => "' . $attributeOption['option_id'] . '",<br />';
        }
        die;
    }
    
    protected function _handleStatus($supplier)
    {
        if($this->supplierStatuses[$supplier] == 0) {
            return $this->statusDisabled;
        }
        
        return $this->statusEnabled ;
    }
    
    protected function _stoneLog($product, $csvArr, $action, $error = null)
    {
        if ($error) {
            $query = 'INSERT INTO stone_log(sku, log_action, payload, payload_hash, errors)
                VALUES("'. $product->getSku() . '", "' . $action . '", "'  . addslashes(json_encode($csvArr)) . '", "' . $this->_getHash($csvArr) . '", "' . $error . '")';
        } else {
            $query = 'INSERT INTO stone_log(sku, log_action, payload, payload_hash)
                VALUES("'. $product->getSku() . '", "' . $action . '", "'  . addslashes(json_encode($csvArr)) . '", "' . $this->_getHash($csvArr) . '")';
        }
        $this->connection->query($query);
    }
    
    protected function _updateCsv()
    {
        $ftp = ftp_connect(
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/host', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/port', $this->storeScope)
        );
        
        $login_result = ftp_login(
            $ftp,
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/user', $this->storeScope),
            $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/pass', $this->storeScope)
        );
        ftp_pasv($ftp, true);
        
        $files = ftp_nlist(
            $ftp,
            ftp_pwd($ftp) . DS . $this->scopeConfig->getValue('forevercompanies_stone_ftp/creds/pattern', $this->storeScope)
        );
        
        foreach ($files as $file) {
            ftp_get($ftp, '/var/www/magento/var/import/diamond_importer.csv', $file);
        }
        
        ftp_close($ftp);
    }
    
}
