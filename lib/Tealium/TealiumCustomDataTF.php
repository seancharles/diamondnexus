<?php

class TealiumExtendData
{
    /** @var  */
    private static $store;

    /** @var  */
    private static $page;

    /** @var \Magento\Framework\App\ObjectManager  */
    private \Magento\Framework\App\ObjectManager $objectManager;

    /** @var string  */
    private string $fcSiteBrand = '1215 Diamonds';

    /** @var int  */
    private int $fcStoreId = 3;

    /**
     * TealiumExtendData constructor.
     */
    public function __construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Sets the store object
     * @param $store
     */
    public static function setStore($store)
    {
        TealiumExtendData::$store = $store;
    }

    /**
     * Sets page object
     * @param $page
     */
    public static function setPage($page)
    {
        TealiumExtendData::$page = $page;
    }

    /**
     * Define our global variables
     * @param $outputAry
     * @return array
     */
    public function setFcGlobals($outputAry)
    {
        $fcGlobals = [
            'site_name' => ['1215diamonds', 'www'],
            'page_name'=> $this->objectManager->get('Magento\Framework\View\Page\Title')->get()
        ];
        return array_merge($fcGlobals, $outputAry);
    }

    /**
     * Retrieves the class value from the page's <body> tag
     * @return false|string[]
     */
    public function getFcBodyClasses() {
        $pageConfig = $this->objectManager->get('Magento\Framework\View\Page\Config');
        $pgBodyClassSrc =  trim($pageConfig->getElementAttribute($pageConfig::ELEMENT_TYPE_BODY, $pageConfig::BODY_ATTRIBUTE_CLASS));
        return explode(' ', $pgBodyClassSrc);
    }

    /**
     * Get the current page url
     * @param string $url
     * @return false|string|string[]
     */
    public function fcGetUrlPath(string $url = '')
    {
        // if no url was provided, lets get the current url
        if (empty($url)) {
            $urlInterface = $this->objectManager->get('Magento\Framework\UrlInterface');
            $url = $urlInterface->getCurrentUrl();
        }

        // parse url
        $parsed = parse_url($url);

        // return the full path of the url
        return array_key_exists('path', $parsed) ? explode('/', $parsed['path']) : '';
    }

    /**
     * Formats a string for Tealium
     * @param string $str
     * @param bool $subSpaces
     * @return array|string|string[]
     */
    public function fcTealFormatStr(string $str, bool $subSpaces = true)
    {
        // lowercase and trim the string
        $output = trim(strtolower($str));

        // if $subSpaces is true, replace spaces with underscores
        if ($subSpaces) {
            $output = str_replace(' ', '_', $output);
        }

        return $output;
    }

    /**
     * Format a slug for Tealium
     * @param string $str
     * @return array|string|string[]|null
     */
    public function fcTealFormatSlug(string $str)
    {
        // lowercase, trim and remove spaces from string
        $output = $this->fcTealFormatStr($str, true);

        // remove any non-alphanumeric characters (and underscore)
        return preg_replace("/[^a-z0-9_]+/i", "", $output);
    }

    /**
     * Format an array of strings
     * @param array $ary
     * @return string
     */
    public function fcTealFormatAry(array $ary): string
    {
        array_map('fcTealFormatStr', $ary);
        return '["' . implode('","', $ary) . '"]';
    }

    /**
     * Format numeric array
     * @param array $ary
     * @return string
     */
    public function fcTealFormatNumAry(array $ary): string
    {
        return '['.implode(',', $ary).']';
    }

    /**
     * Format a price value
     * @param $amt
     * @return string
     */
    public function fcTealFormatPrice($amt): string
    {
        return number_format((float)$amt, 2, '.', '');
    }

    /**
     * Format the utag data array
     * @param $utagDataSrc
     * @return string
     */
    public function fcTealFormatUtagData($utagDataSrc): string
    {
        $utagData = '';
        foreach ($utagDataSrc as $k => $v) {
            if (is_array($v)) {
                $utagData .= ",\n\"" . $k . '": '. $this->fcTealFormatAry($v);
            } else {
                $utagData .= ",\n\"" . $k . '": "' . $v . '"';
            }
        }
        return $utagData;
    }

    /**
     * Get configurable product from simple product id (not currently used)
     * @param $simpleProductId
     * @return string
     */
    public function getFcConfigurableProduct($simpleProductId): string
    {
        return '';
    }

    /**
     * Get the categories for the given product
     * @param $product
     * @return array
     */
    public function fcTealGetProductCats($product)
    {
        $catSrc = $this->fcGetCategories();
        $output = [];
        foreach ($product->getCategoryIds() as $cid) {
            if (isset($catSrc[$cid])) {
                $levelType = ($catSrc[$cid]['navlevel'] == 2) ? 'category' : 'subcategory';
                if (!array_key_exists($levelType, $output)) {
                    $output[$levelType] = [];
                }
                $output[$levelType][] = $catSrc[$cid];
            }
        }
        return $output;
    }

    /**
     * Load category by id (not currently in use)
     * @param int $catId
     * @return mixed
     */
    public function fcGetCategoryById(int $catId)
    {
        return $this->objectManager->get('Magento\Catalog\Model\Category')->load($catId);
    }

    /**
     * Get product by id
     * @param string $productId
     * @return mixed
     */
    public function fcGetProduct(string $productId = '')
    {
        if (!empty($productId)) {
            $product = $this->objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        } else {
            /**
             * @todo m2 registry is deprecated, look into other methods to achieve this
             * see: https://github.com/Vinai/module-current-product-example
             */
            $product = $this->objectManager->get('Magento\Framework\Registry')->registry('current_product');
        }
        return $product;
    }

    /**
     * Get list of product filters (not currently in use)
     * @return mixed
     */
    public function fcGetProductFilters()
    {
        return '';
    }

    /**
     * Get product's image (not currently in use)
     * @param $product
     * @return mixed
     */
    public function fcGetProductImage($product)
    {
        return '';
    }

    /**
     * Get array of all categories for the current store
     * @return array
     */
    public function fcGetCategories()
    {
        $categoryCollection = $this->objectManager->get('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active', 1) // only active categories
            ->setStore(self::$store);


        $categoryData = [];

        foreach ($categories as $category) {
            // get the 'navlevel' to separate categories from subcategories for tealium
            $navLevel = $category->getLevel();

            // if category is 560 (Wedding Rings & Wedding Bands) or 40 (Loose Stones) upgrade to a category (level 2)
            if (in_array($category->getId(), [40, 560])) {
                $navLevel--;
            }

            // if category is 425 (Gifts) or 860 (Clearance) downgrade to a subcategory (level 3)
            if (in_array($category->getId(), [425, 860])) {
                $navLevel++;
            }

            // assign category info to array
            $categoryData[$category->getId()] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $this->fcTealFormatSlug($category->getName()),
                'level' => $category->getLevel(),
                'navlevel' => $navLevel
            ];
        }
        return $categoryData;
    }


    /**
     * Get configurable product id based on simple product id
     * @param $simpleProductId
     * @return mixed
     */
    public function fcGetProductConfigurableId($simpleProductId) {
        $configurableProduct = $this->objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable')
            ->getParentIdsByChild($simpleProductId);
        if (is_array($configurableProduct) && !empty($configurableProduct)) {
            return $configurableProduct[0];
        }
        return $configurableProduct;
    }

    /**
     * Fetch quote from checkout session
     * @return mixed
     */
    public function fcGetQuote()
    {
        return $this->objectManager->get('Magento\Checkout\Model\Session')->getQuote();
    }


    /**
     * Below are functions for handling each individual page type
     */


    /**
     * Handle home page
     * @return array
     */
    public function getHome()
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = [];
        //$outputArray['custom_key'] = "value";

        $outputArray = $this->setFcGlobals($outputArray);
        $outputArray['site_section'] = "home";
        $outputArray['page_type'] = "home";

        $pgCssAry = $this->getFcBodyClasses();
        switch ($pgCssAry[0]) {

            case 'cms-index-index':
                $outputArray['site_section'] = "home";
                $outputArray['page_type'] = "home";
                break;
            case 'customer-account-login':
                $outputArray["site_section"] = 'account';  // Note: could be logging in anywhere
                $outputArray["page_type"] = 'login';
                break;
            case 'customer-account-create':
                $outputArray["site_section"] = 'account'; //  could be created in checkout
                $outputArray["page_type"] = 'register';
                break;
            case 'catalogsearch-result-index':
                $outputArray["site_section"] = 'search';
                $outputArray["tealium_event"] = 'search';
                break;
            default:
                $outputArray["site_section"] = 'default';
                $outputArray["page_type"] = 'default';
        }

        return $outputArray;
    }

    /**
     * Handle search page (not used)
     */
    public function getSearch()
    {
        return [];
    }

    /**
     * Handle category page (not used)
     */
    public function getCategory()
    {
        return [];
    }

    /**
     * Handle product page (not used)
     */
    public function getProductPage()
    {
        return [];
    }

    /**
     * Handle cart page (not used)
     * @return array
     */
    public function getCartPage()
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;
        $pageUrlpaths = $this->fcGetUrlPath();

        $outputArray = [];
        $outputArray = $this->setFcGlobals($outputArray);

        // make sure any product values are in an array
        $outputArray["site_section"] = 'checkout';
        $outputArray["page_type"] = ($pageUrlpaths[2] == 'cart') ? 'cart' : 'checkout';

        $productIds =
        $simpleProductIds =
        $productBrands =
        $productPromoCodes =
        $productCategories =
        $productSubcategories =
        $productImgs =
        $productUrls = [];

        // Get quote info
        $quote = $this->fcGetQuote();
        if ($quote) {
            foreach ($quote->getAllVisibleItems() as $item) {
                // get primary product
                $primaryProduct = $item->getProduct();

                // for now, set child product to match primary product
                $childProduct = $primaryProduct;

                // if primary product is configurable, we need to get the associated simple product and assign as child
                if ($primaryProduct->getTypeId() == 'configurable') {
                    if ($option = $item->getOptionByCode('simple_product')) {
                        $childProduct = $option->getProduct();
                    }
                }

                $productIds[] = $primaryProduct->getId();
                $simpleProductIds[] = $childProduct->getId();
                $productBrands[] = $this->fcSiteBrand;
                $productPromoCodes[] = '';
                $productOnPage[] = '';
                $productImg = $primaryProduct->getImageUrl();
                $productImgs[] = (!empty($productImg)) ? $productImg : '';
                $productUrls[] = (!empty($primaryProduct->getProductUrl())) ? $primaryProduct->getProductUrl() : '';

                $productCats = $this->fcTealGetProductCats($primaryProduct);
                $tmpProductCats = [];
                if (array_key_exists('category', $productCats)) {
                    foreach ($productCats['category'] as $c) {
                        if (array_key_exists('slug', $c) && !empty($c['slug'])) {
                            $tmpProductCats[] = $c['slug'];
                        }
                    }
                }
                if (array_key_exists('subcategory', $productCats)) {
                    foreach ($productCats['subcategory'] as $c) {
                        if (array_key_exists('slug', $c) && !empty($c['slug'])) {
                            $productSubcats[] = $c['slug'];
                        }
                    }
                }

                // limit to one cat/subcat
                if (!empty($tmpProductCats) && !empty($tmpProductCats[0])) {
                    $productCategories[] = $tmpProductCats[0];
                }
                if (!empty($productSubcats) && !empty($productSubcats[0])) {
                    $productSubcategories[] = $productSubcats[0];
                }
            }

            // Add cart quote id
            $outputArray["cart_id"] = $quote->getEntityId();
        }

        if (count($productIds) > 0) {
            $outputArray["product_id"] = $productIds;
            $outputArray["product_simple_id"] = $simpleProductIds;
            $outputArray["product_brand"] = $productBrands;
            $outputArray["product_promo_code"] = $productPromoCodes;
            $outputArray["product_category"] = $productCategories;
            $outputArray["product_subcategory"] = $productSubcategories;
            $outputArray["product_image_url"] = $productImgs;
            $outputArray["product_url"] = $productUrls;
        }

        return $outputArray;
    }

    /**
     * Handle CMS page
     * @return array
     */
    public function getCmsPage()
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        $outputArray = $this->setFcGlobals($outputArray);

        $pgCssAry = $this->getFcBodyClasses();
        switch ($pgCssAry[1]) {
            case 'customer-account':
                $outputArray["site_section"] = 'account';
                $outputArray["page_type"] = 'account';
                break;
            default:
                $outputArray["site_section"] = 'default';
                $outputArray["page_type"] = 'default';
        }

        return $outputArray;
    }

    /**
     * Handle blog page (not used)
     */
    public function getBlog()
    {
        return [];
    }

    /**
     * Handle order confirmation page (not used)
     * @return array
     */
    public function getOrderConfirmation()
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = [];
        $outputArray = $this->setFcGlobals($outputArray);
        $outputArray["site_section"] = 'checkout';
        $outputArray["page_type"] = 'order';

        $productIds =
        $simpleProductIds =
        $productBrands =
        $productPromoCodes =
        $productCategories =
        $productSubcategories =
        $productImgs =
        $productUrls = [];

        $orderModel = $this->objectManager->get('Magento\Sales\Model\Order');

        if ($orderModel) {
            $order = $orderModel->loadByIncrementId($page->getOrderId());

            foreach ($order->getAllVisibleItems() as $item) {

                // get primary product id
                $tmpPrimaryProduct = $item->getProduct();

                // for now, set child product to match primary product
                $tmpChildProduct = $tmpPrimaryProduct;

                // if primary product is configurable, we need to get the associated simple product and assign as child
                if ($tmpPrimaryProduct->getTypeId() == 'configurable') {
                    if ($option = $item->getOptionByCode('simple_product')) {
                        $tmpChildProduct = $option->getProduct();
                    }
                }

                // now get the product models
                $primaryProduct = $this->fcGetProduct($tmpPrimaryProduct->getId());
                $childProduct = $this->fcGetProduct($tmpChildProduct->getId());


                $productIds[] = $primaryProduct->getId();
                $simpleProductIds[] = $childProduct->getId();
                $productBrands[] = $this->fcSiteBrand;
                $productPromoCodes[] = '';
                $productOnPage[] = '';
                $productImg = $primaryProduct->getImageUrl();
                $productImgs[] = (!empty($productImg)) ? $productImg : '';
                $productUrls[] = (!empty($primaryProduct->getProductUrl())) ? $primaryProduct->getProductUrl() : '';

                $productCats = $this->fcTealGetProductCats($primaryProduct);
                $tmpProductCats = [];
                if (array_key_exists('category', $productCats)) {
                    foreach ($productCats['category'] as $c) {
                        if (array_key_exists('slug', $c) && !empty($c['slug'])) {
                            $tmpProductCats[] = $c['slug'];
                        }
                    }
                }
                if (array_key_exists('subcategory', $productCats)) {
                    foreach ($productCats['subcategory'] as $c) {
                        if (array_key_exists('slug', $c) && !empty($c['slug'])) {
                            $productSubcats[] = $c['slug'];
                        }
                    }
                }

                // limit to one cat/subcat
                if (!empty($tmpProductCats) && !empty($tmpProductCats[0])) {
                    $productCategories[] = $tmpProductCats[0];
                }
                if (!empty($productSubcats) && !empty($productSubcats[0])) {
                    $productSubcategories[] = $productSubcats[0];
                }
            }
        }

        if (count($productIds) > 0) {
            $outputArray["product_id"] = $productIds;
            $outputArray["product_simple_id"] = $simpleProductIds;
            $outputArray["product_brand"] = $productBrands;
            $outputArray["product_promo_code"] = $productPromoCodes;
            $outputArray["product_category"] = $productCategories;
            $outputArray["product_subcategory"] = $productSubcategories;
            $outputArray["product_image_url"] = $productImgs;
            $outputArray["product_url"] = $productUrls;
        }

        $outputArray["customer_street_1"] = $order->getBillingAddress()->getStreet1();
        $outputArray["customer_street_2"] = $order->getBillingAddress()->getStreet2();
        $outputArray["customer_city"] = $order->getBillingAddress()->getCity(); // $order->getBillingAddress()->getData('city')
        $outputArray["country_code"] = $order->getBillingAddress()->getCountryId();
        $outputArray["customer_postal_code"] = $order->getBillingAddress()->getPostcode();
        $outputArray["customer_first_name"] = $order->getCustomerFirstname();
        $outputArray["customer_last_name"] = $order->getCustomerLastname();
        $outputArray["customer_country"] = $order->getBillingAddress()->getCountryId();
        $outputArray["customer_state"] = $order->getBillingAddress()->getRegion();
        $outputArray["order_currency_code"] = $order->getOrderCurrencyCode();
        $outputArray["order_discount_amount"] = $order->getDiscountAmount();
        $outputArray["order_merchandise_total"] = $order->getGrandTotal();
        $outputArray["order_promo_code"] = "";
        $outputArray["order_shipping_amount"] = $order->getShippingAmount();
        $outputArray["order_shipping_type"] = $order->getShippingDescription();

        $outputArray["order_store"] = $order->getStoreId();
        $outputArray["order_tax_amount"] = $order->getTaxAmount();
        $outputArray["order_type"] = 'www';
        $outputArray["order_status"] = $order->getStatus();

        return $outputArray;
    }

    /**
     * Handle customer account page
     * @return array
     */
    public function getCustomerData()
    {
        $store = TealiumExtendData::$store;
        $page = TealiumExtendData::$page;

        $outputArray = array();
        $outputArray = $this->setFcGlobals($outputArray);

        $outputArray["site_section"] = 'account';
        $outputArray["page_type"] = 'account';


        return $outputArray;
    }
}

/** ************** */

// set store and page
TealiumExtendData::setStore($data["store"]);
TealiumExtendData::setPage($data["page"]);

$udoElements = array(
    'Home' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getHome();
    },
    'Search' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getSearch();
    },
    'Category' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCategory();
    },
    'ProductPage' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getProductPage();
    },
    'CmsPage' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCmsPage();
    },
    'Blog' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getBlog();
    },
    'Cart' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCartPage();
    },
    'Confirmation' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getOrderConfirmation();
    },
    'Customer' => function() {
        $tealiumData = new TealiumExtendData();
        return $tealiumData->getCustomerData();
    }
);
