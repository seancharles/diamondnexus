<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\File\Csv;
use Magento\Catalog\Api\ProductRepositoryInterface;


class StoneImport
{    
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;
    
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
    protected $productRepository;
    protected $requiredFieldsArr;
    
    
    public function __construct(
        CollectionFactory $collectionFactory,
        Product $productFactory,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo,
        Csv $cs,
        ProductRepositoryInterface $productRepositoryInterface
        ) {
            $this->productCollectionFactory = $collectionFactory;
            $this->productModel = $productFactory;
            $this->resourceConnection = $resource;
            $this->attributeSetMod = $attributeSetRepo;
            $this->stockItemModel = $stockItemRepo;
            $this->csv = $cs;
            $this->productRepository = $productRepositoryInterface;
            
            $this->csvHeaderMap = array(
                "Product Name" => "name",
         //       "Supplier" => "supplier",
                "Certificate #" => "sku",
         //       "Shape" => "shape",
                "Lab" => "lab",
                "Weight" => "weight",
        //        "Color" => "color",
        //        "Clarity" => "clarity",
        //        "Cut Grade" => "cut_grade",
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
           //     "Delivery Date" => "shipping_status",
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
            
            // clarity_sort
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
            
            // cut_grade_sort
            $this->cutGradeSortMap = array(
                "Good" => "100",
                "Very Good" => "200",
                "Excellent" => "300",
                "Ideal" => "400",
                // TODO: Remove. Adding to get through import.
                "G" => ""
            );
            
            // color_sort
            $this->colorSortMap = array(
                "J" => "100",
                "I" => "200",
                "H" => "300",
                "G" => "400",
                "F" => "500",
                "E" => "600",
                "D" => "700"
            );
            
            // shape_pop_sort
            $this->shapePopMap = array(
                "Round" => "100",
                "Princess" => "200",
                "Cushion" => "300",
                "Oval" => "400",
                "Emerald" => "500",
                "Pear" => "600",
                "Asscher" => "700",
                "Radiant" => "800",
                "Marquise" => "900",
                "Heart" => "1000"
            );
            
            // shape_alpha_sort
            $this->shapeAlphaMap = array(
                "Round" => "1000",
                "Princess" => "800",
                "Cushion" => "200",
                "Oval" => "600",
                "Emerald" => "300",
                "Pear" => "700",
                "Asscher" => "100",
                "Radiant" => "900",
                "Marquise" => "500",
                "Heart" => "400"
            );
            
            // shipping_status
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
            //    "Product Name",
                "Supplier",
                "Certificate #",
                "Shape",
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
                "Excellent" => "2876",
                "Ex" => "2876",
                "Not Specified" => "3076",
                "Ideal" => "2877",
                "Very Good" => "2878",
                "Very good" => "2878",
                "Good" => "2879",
                // TODO: Create this attribute option and place its value here.
                "Fair" => "",
                // TODO: Remove. Adding to get through import.
                "G" => "",
                "-" => "",
                "None" => ""
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
                "Princess" => "2843",
                "Asscher" => "2844",
                "Cushion" => "2845",
                "Heart" => "2846",
                "Oval" => "2847",
                "Emerald" => "2848",
                "Radiant" => "2849",
                "Pear" => "2850",
                "Marquise" => "2851"
            );
            
            $this->supplierMap = array(
                "blumoon" => "3533",
                "classic" => "3534",
                "greenrocks" => "3535",
                "internal" => "3536",
                "labrilliante" => "3537",
                "paradiam" => "3538",
                "pdc" => "3539",
                "stuller" => "3540",
                "washington" => "3541",
                "foundry" => "3542",
                "diamondfoundry" => "3542",
                "meylor" => "3543",
                "ethereal" => "3544",
                "smilingrocks" => "3545",
                "unique" => "3546",
                "qualitygold" => "3547",
                "flawlessallure" => "3548",
                "labs" => "3549",
                "labsdiamond" => "3549",
                "Fenix" => "3550",
                "fenix" => "3550",
                "brilliantdiamonds" => "3551",
                "growndiamondcorpusa" => "3552",
                "internationaldiamondjewelry" => "3553",
                "ecogrown" => "3554",
                "purestones" => "3555",
                "proudest" => "3556",
                "proudestlegendlimited" => "3556",
                "dvjcorp" => "3357",
                "dvjewelrycorporation" => "3557",
                "indiandiamonds" => "3558",
                "growndiamondcorp" => "3559",
                "lush" => "3560",
                "lushdiamonds" => "3560",
                "altr" => "3561",
                "Forever Grown" => "3562",
                "internalaltr" => "3563",
                // TODO: Create these attribute options and place their values here.
                "bhakti" => ""
            );
            
            
            
            
        //    $this->fileName = $_SERVER['HOME'] . 'magento/var/import/stone_import.csv';
            $this->fileName = $_SERVER['HOME'] . 'magento/var/import/stone_import_full.csv';
            
            // product_type = Diamonds
            // Attribute Set = Loose Diamonds
    }
    
    function run()
    {
        
   //     $this->_getMapForAttribute();
        
        
        $csvArray = $this->_buildArray();
        
        // product load = 8s, 10s, 8s, 7s, 8s
        // repo load    = 9s, 8s, 10s, 8s, 8s, 8s, 
        // date("d-m-Y h:i:s")
        
        echo 'time before - ' . date("d-m-Y h:i:s") . '<br />';
        $i = 0; 
        foreach ($csvArray as $csvArr) {
            
            if (1==0 && $i < 22388)
            {
                $i++;
                continue;
            //    break;
            }
            
            $productId = $this->productModel->getIdBySku($csvArr['Certificate #']);
            if ($productId) {
                $product = $this->productModel->load($productId); // faster from a few tests.
           //   $product = $this->productRepository->getById($productId);
             
                
                $rowHash = hash('sha1', json_encode($csvArr)); 
                if ($product->getProductHash() !== $rowHash) { // what if product is new?
                   
                    if (!$this->_checkForRequiredFields($csvArr)) {
                        continue;
                    }
            //        echo $this->colorMap[$csvArr['Color']];die;
                    $product->setProductHash($rowHash);
                    $product->setProductType('3569'); // diamond
                    
                    
                    // These passed the required check and have their own maps.
                    // Need to add isset checks for these.
                    try {
                        $product->setColor($this->colorMap[$csvArr['Color']]);
                        $product->setClarity($this->clarityMap[$csvArr['Clarity']]);
                        $product->setCutGrade($this->cutGradeMap[$csvArr['Cut Grade']]);
                        $product->setShape($this->shapeMap[$csvArr['Shape']]);
                        $product->setSupplier($this->supplierMap[$csvArr['Supplier']]);
                    } catch(Exception $e)
                    {
                        echo 'zzz error ' . $e->getMessage() . '<br />';
                    }
                    
                    
                    
                    
                    // Sorting
                    if (isset($this->claritySortMap[$csvArr['Clarity']])) {
                        $product->setClaritySort($this->claritySortMap[$csvArr['Clarity']]);
                    }
                    if (isset($this->colorSortMap[$csvArr['Color']])) {
                        $product->setColorSort($this->colorSortMap[$csvArr['Color']]);
                    }
                    if (isset($this->cutGradeSortMap[$csvArr['Cut Grade']])) {
                        $product->setCutGradeSort($this->cutGradeSortMap[$csvArr['Cut Grade']]);
                    }
                    if (isset($this->shapePopMap[$csvArr['Shape']])) {
                        $product->setShapePopSort($this->shapePopMap[$csvArr['Shape']]);
                    }
                    if (isset($this->shapeAlphaMap[$csvArr['Shape']])) {
                        $product->setShapeAlphaSort($this->shapeAlphaMap[$csvArr['Shape']]);
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
                        
                    $product->setShape('2842');
                    
                    $product->save();
                    echo 'saved ' . $product->getName() . '<br />';
                }
              
            }
            else { // else new product
                
            }
            
            
            $i++;
            
        }
        
        echo 'time after - ' . date("d-m-Y h:i:s") . '<br />';;
        
        
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
        //echo $arr['Product Name'];
        // ????
        
        foreach ($this->requiredFieldsArr as $req) {
            if (!isset($arr[$req]) || trim($arr[$req]) == "" || $arr[$req] == "Nan") {
                return false;
            }
        }
      
        return true;
    }
    
    protected function _getMapForAttribute()
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
        
        
        echo $attributeCode . '<br /><br />';
        
        foreach ($attributeOptionAll->getData() as $attributeOption)
        {
            
            
            echo '"' . $attributeOption['default_value'] . '" => "' . $attributeOption['option_id'] . '",<br />';
            
        }
        die;
        echo '<pre>';
        var_dump("attribute info", $attributeOptionAll->getData());
        
        die;
    }
    
}