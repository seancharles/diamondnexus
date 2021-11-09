<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Reports\Model\ResourceModel\Product\Sold\CollectionFactory as SoldProductCollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\CustomSales\Helper\Producer;
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
use Magento\Store\Model\ScopeInterface;
use Zend_Db_Select_Exception;

class StoneImport
{
    protected CollectionFactory $productCollectionFactory;
    protected ProductFactory $productFactory;
    protected Product $productModel;
    protected ResourceConnection $resourceConnection;
    protected AttributeSetRepositoryInterface $attributeSetMod;
    protected StockItemRepository $stockItemModel;
    protected string $mediaTmpDir;
    protected File $file;
    protected AdapterInterface $connection;
    protected Producer $producerHelper;
    protected ProductAction $productAction;
    protected AttributeRepositoryInterface $eavAttributeRepository;

    protected array $booleanMap;
    protected array $csvHeaderMap;
    protected array $clarityMap;
    protected array $cutGradeMap;
    protected array $colorMap;
    protected array $shapeMap;
    protected array $supplierMap;
    protected array $onlineMap;

    protected array $labReportMap;
    protected array $polishGradeMap;
    protected array $symmetryGradeMap;

    protected array $attributesWithOptions = [
        'lab_report' => 'labReportMap',
        'polish_grade' => 'polishGradeMap',
        'symmetry_grade' => 'symmetryGradeMap'
    ];

    protected array $shapePopMap;
    protected array $shapeAlphaMap;
    protected array $shippingStatusMap;

    protected array $claritySortMap;
    protected array $cutGradeSortMap;
    protected array $colorSortMap;

    protected Csv $csv;

    protected string $fileName;
    protected array $requiredFieldsArr;
    protected SoldProductCollectionFactory $soldProductCollectionFactory;
    protected ProductRepository $productRepo;

    protected int $statusEnabled;
    protected int $statusDisabled;

    protected array $supplierStatuses;

    protected ScopeConfigInterface $scopeConfig;
    protected string $storeScope;

    /**
     * @throws LocalizedException
     * @throws FileSystemException
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Product $productModel,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepositoryInterface,
        StockItemRepository $stockItemRepository,
        Csv $csv,
        DirectoryList $directoryList,
        File $file,
        SoldProductCollectionFactory $soldProductCollection,
        ProductRepository $productRepository,
        ScopeConfigInterface $scopeConfigInterface,
        Producer $producer,
        ProductAction $productAction,
        AttributeRepositoryInterface $eavAttributeRepository
    ) {
        $this->productCollectionFactory = $collectionFactory;
        $this->productModel = $productModel;
        $this->productFactory = $productFactory;
        $this->resourceConnection = $resource;
        $this->attributeSetMod = $attributeSetRepositoryInterface;
        $this->stockItemModel = $stockItemRepository;
        $this->csv = $csv;
        $this->mediaTmpDir = $directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
        $this->file = $file;
        $this->soldProductCollectionFactory = $soldProductCollection;
        $this->productRepo = $productRepository;
        $this->scopeConfig = $scopeConfigInterface;
        $this->producerHelper = $producer;
        $this->productAction = $productAction;
        $this->eavAttributeRepository = $eavAttributeRepository;

        $this->file->checkAndCreateFolder($this->mediaTmpDir);
        $this->connection = $resource->getConnection();

        $this->storeScope = ScopeInterface::SCOPE_STORE;

        $this->csvHeaderMap = array(
            "Product Name" => "name",
            "Certificate #" => "sku",
            "Weight" => "carat_weight",
            "Length" => "length",
            "Width" => "width",
            "Depth (mm)" => "depth_mm",
            "Length to Width" => "length_width_ratio",
            "Depth %" => "depth_percent",
            "Measurements" => "measurements",
            "Table %" => "table_percent",
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
            "Video" => "video_url"
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
            "Super Ideal" => "500"
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
            "Heart" => "950",
            "heart" => "950"
        );

        $this->shapeAlphaMap = array(
            "Round" => "950",
            "round" => "950",
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

        // @todo this should map to shipperhq_shipping_group attribute
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
            "Width",
            "Cost"
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
            "super ideal" => "2877", // @todo add "Super Ideal" to cut_grade as option
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
            "forever grown" => "35",
            "internalaltr" => "36",
            "internalALTR" => "36",
            "bhaktidiamond" => "37",
            "bhakti" => "37"
        );

        $this->onlineMap = [
            'yes' => "3448",
            'no' => "3447"
        ];

        $this->fileName = $_SERVER['HOME'] . 'magento/var/import/diamond_importer.csv';

        $this->statusEnabled = Status::STATUS_ENABLED;
        $this->statusDisabled = Status::STATUS_DISABLED;

        $supplierData = $this->connection->fetchAll("SELECT `enabled`, `code` FROM `stones_supplier`");
        $this->supplierStatuses = array();
        foreach ($supplierData as $supplierD) {
            $this->supplierStatuses[strtolower($supplierD['code'])] = $supplierD['enabled'];
            if ($supplierD['code'] == "bhaktidiamond") {
                $this->supplierStatuses["bhakti"] = $supplierD['enabled'];
            } elseif ($supplierD['code'] == "diamondfoundry") {
                $this->supplierStatuses["foundry"] = $supplierD['enabled'];
            } elseif ($supplierD['code'] == "labs") {
                $this->supplierStatuses["labsdiamond"] = $supplierD['enabled'];
            } elseif ($supplierD['code'] == "Fenix") {
                $this->supplierStatuses["fenix"] = $supplierD['enabled'];
            } elseif ($supplierD['code'] == "proudestlegendlimited") {
                $this->supplierStatuses["proudest"] = $supplierD['enabled'];
            } elseif ($supplierD['code'] == "lushdiamonds") {
                $this->supplierStatuses["lush"] = $supplierD['enabled'];
            }
        }
    }

    private function setAttributeOptionsMap($attributeCode, $mapVar)
    {
        // load the attribute
        $attribute = $this->eavAttributeRepository->get(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $attributeCode
        );

        // get the option labels and values
        $options = [];
        $attributeOptions = $attribute->getSource()->getAllOptions(false);
        foreach ($attributeOptions as $option) {
            $options[strtolower(trim($option['label']))] = $option['value'];
        }

        // set the class map variable
        $this->${$mapVar} = $options;

        unset($attribute);
        unset($options);
        unset($attributeOptions);
    }

    /**
     * @throws NoSuchEntityException
     * @throws Zend_Db_Select_Exception
     * @throws StateException
     */
    public function deleteUnsoldDiamonds(): StoneImport
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

    public function run($fullImport = false)
    {
        // determine what csv file to be processed
        if ($fullImport) {
            $this->fileName = $_SERVER['HOME'] . 'magento/var/import/full_diamond_import.csv';
        } else {
            $this->updateCsv();
        }

        // get the options for our variables
        foreach ($this->attributesWithOptions as $attributeCode => $mapVar) {
            $this->setAttributeOptionsMap($attributeCode, $mapVar);
        }

        // generate array of data to be processed from csv file
        $csvArray = $this->buildArray();

        // array to hold what id's we need to set visibility attribute on after processing
        $idsToSetVisibility = [];

        // loop through each record of the csv
        foreach ($csvArray as $csvArr) {
            try {
                // verify all required fields exist in this record, including Certificate #
                // if they do not exist, log error and proceed to next record
                if (!$this->checkForRequiredFields($csvArr)) {
                    $product = new DataObject();
                    if (isset($csvArr['Certificate #'])) {
                        $product->setSku($csvArr['Certificate #']);
                    }
                    $this->stoneLog($product, $csvArr, "error", "Required field invalid.");
                    continue;
                }

                // see if we have an existing product with the current Certificate #
                // if we do, then we know we are editing an existing product, else we're adding new product
                $productId = $this->productModel->getIdBySku($csvArr['Certificate #']);
                if ($productId) {
                    $product = $this->productModel->load($productId);

                    // if existing product has been disabled assume it has been sold
                    // (or supplier was disabled, which will end up with product being deleted later)
                    if ($product->getStatus() == $this->statusDisabled) {
                        unset($productId);
                        unset($product);
                        continue;
                    }

                    // apply all data from csv to this product and save it
                    $success = $this->applyCsvRowToProduct($product, $csvArr);

                    // if save was successful, enter an 'update' log entry
                    if ($success) {
                        $this->stoneLog($product, $csvArr, "update");
                    }
                } else { // else new product
                    $product = $this->productFactory->create();

                    // Because images are slowing down the product save, it was agreed to forgo loading images into
                    // magento. Instead, we'll just use the image url stored in the attribute diamond_img_url.
                    /**
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
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->stoneLog(
                    $product,
                    $csvArr,
                    "error",
                    "New Product " . $csvArr['Certificate #'] . " not created. Incorrect image extension"
                    );
                    //continue;
                    }
                    } else {
                    $this->stoneLog(
                    $product,
                    $csvArr,
                    "error",
                    "New Product " . $csvArr['Certificate #'] . " not created. No image."
                    );
                    //continue;
                    }
                     **/

                    $product->setName(reset($csvArr));
                    $product->setTypeId('simple');
                    $product->setAttributeSetId(31);
                    $product->setSku($csvArr['Certificate #']);
                    $product->setStatus($this->statusEnabled);
                    $product->setVisibility(1);

                    // From the admin, the reps can use a diamond on a 1215 or FA order. On the frontend we do not
                    // display diamonds on FA. Need to assign visibility for each store somehow.
                    $product->setWebsiteIds(array(2, 3));
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

                    $success = $this->applyCsvRowToProduct($product, $csvArr);

                    if ($success) {
                        $this->stoneLog($product, $csvArr, "add");
                        // 1215 storefront visibility.
                        // The original code below was calling a second ->save() function which is taking 3+ minutes
                        // to run. To save time, instead of calling another save, we are logging the id's in an array
                        // and using the productAction->updateAttributes() method, done at the end of the script.
                        //
                        //$product->setStoreId(12)->setVisibility(4)->save();

                        $productId = $product->getEntityId();
                        $idsToSetVisibility[] = $productId;
                    }

                    unset($imageFileName);
                    unset($imageResult);
                }

                unset($productId);
                unset($product);
                unset($csvArr);
            } catch (
            PluginAuthenticationException | ExpiredException | InitException | InputMismatchException | InvalidTransitionException | UserLockedException | TemporaryStateCouldNotSaveException
            | AbstractAggregateException | AlreadyExistsException | AuthenticationException | AuthorizationException | BulkException | ConfigurationMismatchException | CouldNotDeleteException
            | CouldNotSaveException | CronException | EmailNotConfirmedException | FileSystemException | InputException | IntegrationException | InvalidArgumentExceptionm | InvalidEmailOrPasswordException
            | LocalizedException | MailException | NoSuchEntityException | NotFoundException | PaymentException | RemoteServiceUnavailableException | RuntimeException | SecurityViolationException
            | SerializationException | SessionException | StateException | ValidatorException $e
            ) {
                $product = new DataObject();
                if (isset($csvArr['Certificate #'])) {
                    $product->setSku($csvArr['Certificate #']);
                }
                $this->stoneLog(
                    $product,
                    $csvArr,
                    "error",
                    $csvArr['Certificate #'] . " not processed. " . $e->getMessage()
                );
            } catch (Exception $e) {
                $product = new DataObject();
                if (isset($csvArr['Certificate #'])) {
                    $product->setSku($csvArr['Certificate #']);
                }
                $this->stoneLog(
                    $product,
                    $csvArr,
                    "error",
                    $csvArr['Certificate #'] . " not processed. " . $e->getMessage()
                );
            }
        } // end foreach ($csvArray as $csvArr)

        // set tf visibility
        if (!empty($idsToSetVisibility)) {
            $this->productAction->updateAttributes($idsToSetVisibility, array('visibility' => 4), 12);
        }

        $this->cleanLogs();
        $this->producerHelper->flushElderCache();
    }

    protected function applyCsvRowToProduct($product, $csvArr): bool
    {
        $product->setFcProductType('3569'); //diamond

        // set weight to 1 for shipperhq purposes
        $product->setWeight(1);

        // These have been checked as required fields.
        $product->setColor($this->colorMap[$csvArr['Color']]);
        $product->setClarity($this->clarityMap[$csvArr['Clarity']]);
        $product->setCutGrade($this->cutGradeMap[strtolower($csvArr['Cut Grade'])]);
        $product->setShape($this->shapeMap[$csvArr['Shape Name']]);
        $product->setSupplier(strtolower($this->supplierMap[strtolower($csvArr['Supplier'])]));

        if (array_key_exists(strtolower(trim($csvArr['Lab'])), $this->labReportMap)) {
            $product->setLabReport($this->labReportMap[strtolower(trim($csvArr['Lab']))]);
        }

        if (array_key_exists(strtolower(trim($csvArr['Polish'])), $this->polishGradeMap)) {
            $product->setPolishGrade($this->polishGradeMap[strtolower(trim($csvArr['Polish']))]);
        }

        if (array_key_exists(strtolower(trim($csvArr['Symmetry'])), $this->symmetryGradeMap)) {
            $product->setSymmetryGrade($this->symmetryGradeMap[strtolower(trim($csvArr['Symmetry']))]);
        }

        if (array_key_exists(strtolower($csvArr['Online']), $this->onlineMap)) {
            $product->setOnline($this->onlineMap[strtolower($csvArr['Online'])]);
        }

        // @todo there is no super_ideal product attribute?
        if (isset($csvArr['Super Ideal'])) {
            $product->setSuperIdeal($csvArr['Super Ideal']);
        }

        if (isset($this->supplierStatuses[strtolower($csvArr['Supplier'])])) {
            $this->handleStatus($csvArr['Supplier']);
        } else {
            $this->stoneLog($product, $csvArr, "error", "Supplier does not exist - " . $csvArr['Supplier']);
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

        // Delivery Date - @todo this should map to shipperhq_shipping_group
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

    public function buildArray(): array
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
                foreach ($val as $k => $value) {
                    $arr[$i][$fields[$k]] = $value;
                }
                $i++;
            }
        }
        return $arr;
    }

    protected function checkForRequiredFields($arr)
    {
        foreach ($this->requiredFieldsArr as $req) {
            if (!isset($arr[$req]) || trim($arr[$req]) == "" || $arr[$req] == "Nan") {
                return false;
            }
        }
        return true;
    }

    protected function cleanLogs()
    {
        $query = "DELETE FROM stone_log
        WHERE log_date < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))";

        $this->connection->query($query);
    }

    protected function getHash($csvArr)
    {
        return hash('sha1', json_encode($csvArr));
    }

    protected function handleStatus($supplier)
    {
        if ($this->supplierStatuses[strtolower($supplier)] == 0) {
            return $this->statusDisabled;
        }

        return $this->statusEnabled;
    }

    protected function stoneLog($product, $csvArr, $action, $error = null)
    {
        if ($error) {
            $query = 'INSERT INTO stone_log(sku, log_action, payload, payload_hash, errors)
                VALUES("' . $product->getSku() . '", "' . $action . '", "' . addslashes(
                    json_encode($csvArr)
                ) . '", "' . $this->getHash($csvArr) . '", "' . $error . '")';
        } else {
            $query = 'INSERT INTO stone_log(sku, log_action, payload, payload_hash)
                VALUES("' . $product->getSku() . '", "' . $action . '", "' . addslashes(
                    json_encode($csvArr)
                ) . '", "' . $this->getHash($csvArr) . '")';
        }
        $this->connection->query($query);
    }

    public function updateCsv()
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
            ftp_pwd($ftp) . DS . $this->scopeConfig->getValue(
                'forevercompanies_stone_ftp/creds/pattern',
                $this->storeScope
            )
        );

        foreach ($files as $file) {
            ftp_get($ftp, '/var/www/magento/var/import/diamond_importer.csv', $file);
        }

        ftp_close($ftp);
    }
}
