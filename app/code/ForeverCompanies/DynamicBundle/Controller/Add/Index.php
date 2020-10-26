<?php

    namespace ForeverCompanies\DynamicBundle\Controller\Add;

    use Magento\Framework\Event\ManagerInterface as EventManager;
    use Magento\Framework\App\Action\Context;
    use Magento\Checkout\Model\Cart;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $eventManager;
    protected $cart;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $guestCart;
    protected $productRepository;
    protected $cartItemFactory;
    protected $productloader;
    protected $optioncollection;
    protected $itemoption;
    protected $shipperLogger;

    protected $bundleIdentity;
    protected $bundleSelectionProductIds;
    protected $bundleDynamicOptionIds;
    protected $test;

    public function __construct(
        EventManager $eventManager,
        Context $context,
        Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $guestCart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory $optioncollection,
        \Magento\Quote\Model\Quote\Item\Option $itemoption,
        \ShipperHQ\Shipper\Helper\LogAssist $shipperLogger,
        \ForeverCompanies\CustomAttributes\Helper\TransformData $test
    ) {
        parent::__construct($context);
        $this->eventManager = $eventManager;
        $this->cart = $cart;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->guestCart = $guestCart;
        $this->productRepository = $productRepository;
        $this->cartItemFactory = $cartItemFactory;
        $this->productloader = $productloader;
        $this->optioncollection = $optioncollection;
        $this->itemoption = $itemoption;
        $this->shipperLogger = $shipperLogger;
        $this->test = $test;
    }


    /**
     * add cart item
     */
    public function execute()
    {
        try {
            $post = $this->getRequest()->getParams();
            $bundleId = $post['product'];
            $dynamicId = $post['dynamic_bundled_item_id'];
            $options = $post['options'];

            // added to handle standard bundled options
            $bundleProductSelections = $post['bundle_option'];
            $bundleCustomOptions = $post['bundle_custom_option'];

            // get the quote id
            $quoteId = $this->cart->getQuote()->getId();

            if (!$quoteId > 0) {
                // save the quote to create an instance
                $this->cart->saveQuote();

                // fetch the card id again
                $quoteId = $this->cart->getQuote()->getId();
            }

            // load the quote using quote repository
            $quote = $this->quoteRepository->get($quoteId);

            if ($bundleId > 0 && $dynamicId > 0) {
                $bundleProductModel = $this->productloader->create()->load($bundleId);
                $dynamicProductModel = $this->productloader->create()->load($dynamicId);

                // get the identity for the product to identify uniquely
                $this->getBundleIdentity($bundleProductModel, $dynamicId, $bundleProductSelections);

                // pulls all associated bundle options for item with no selection
                $bundleOptions = $this->getBundleOptions($bundleProductModel);

                // gets the bundle options for the specific item in cart with selection
                $bundleOptionValues = $this->formatBundleOptionSelection();

                $parentItem = $this->addParentItem($bundleProductModel, $dynamicProductModel, $options);
                $quote->addItem($parentItem);
                $quote->save();
                $parentItemId = $this->getLastQuoteItemId($quoteId);

                $itemOptions = [
                    'info_buyRequest' => json_encode([
                        // read more: https://maxchadwick.xyz/blog/wtf-is-uenc
                        'uenc' => '', // no url redirect on add to cart
                        'product' => $bundleId,
                        'selected_configurable_option' => '',
                        'related_product' => '',
                        'item' => $bundleId,
                        'bundle_option' => $bundleOptionValues,
                        'dynamic_bundled_item_id' => $dynamicId,
                        'dynamic_custom_options' => ((isset($bundleCustomOptions) == true) ? $bundleCustomOptions: []),
                        'options' => $options,
                        'qty' => "1"
                    ]),
                    'bundle_identity' =>  $this->bundleIdentity
                ];

                $this->formatBundleOptionsParent($itemOptions, $options);
                $this->formatBundleOptionIds($itemOptions, $bundleOptions);
                $this->formatBundleSelectionsParent($itemOptions);
                $this->setItemOptions($parentItemId, $bundleId, $itemOptions);

                // iterate through native bundle options
                foreach ($this->bundleSelectionProductIds as $selectionId => $bundle) {
                    // implements the dynamic product when enabled
                    if (in_array($bundle['option_id'], $this->bundleDynamicOptionIds) == true) {
                        $childId = $dynamicId;
                    } else {
                        $childId = $bundle['product_id'];
                    }

                    $childProductModel = $this->productloader->create()->load($childId);

                    // parse out the custom options for the selection
                    if (isset($bundleCustomOptions[$selectionId][$bundle['product_id']]) == true) {
                        $childCustomOptions = $bundleCustomOptions[$selectionId][$bundle['product_id']];
                    } else {
                        $childCustomOptions = [];
                    }

                    // child item handling
                    $childItem = $this->addChildItem($childProductModel, $parentItemId, $childCustomOptions);
                    $quote->addItem($childItem);
                    $quote->save();
                    $itemId = $this->getLastQuoteItemId($quoteId);

                    $itemOptions = [
                        'info_buyRequest' => json_encode([
                            // read more: https://maxchadwick.xyz/blog/wtf-is-uenc
                            'uenc' => '', // no url redirect on add to cart
                            'product' => $childId,
                            'selected_configurable_option' => '',
                            'related_product' => '',
                            'item' => $bundleId,
                            'bundle_option' => $bundleOptionValues,
                            // conditionally set child custom option values if they are provided
                            'options' => $childCustomOptions,
                            'qty' => 1
                        ]),
                        'bundle_identity' => $this->bundleIdentity
                    ];

                    $this->formatBundleOptionIds($itemOptions, $bundleOptions);
                    $this->formatBundleSelectionsChild($itemOptions, $selectionId, $bundle);
                    $this->setItemOptions($itemId, $childId, $itemOptions);
                }

            }

            $quote->collectTotals()->save();

            // dispatch add to cart method to allow other modules to implement customization
            $this->eventManager->dispatch('tf_cart_product_add_after', ['parent' => $parentItem, 'child' => $childItem]);

            $this->eventManager->dispatch(
                'checkout_cart_product_add_after',
                ['quote_item' => $parentItem, 'product' => $bundleProductModel]
            );

            $message = __(
                'You added %1 to your shopping cart.',
                $bundleProductModel->getName()
            );

            $this->messageManager->addSuccessMessage($message);

            return $this->resultRedirectFactory->create()->setUrl($this->_url->getUrl('checkout/cart'));
            exit;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * get last cart item added
     */
    private function getLastQuoteItemId($quoteId = 0)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collecion = $_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\Collection')->addFieldToFilter('quote_id', $quoteId);

        $lastitem = $collecion->getLastItem();

        return $lastitem->getId();
    }

    /**
     * format bundle custom options
     */
    private function formatBundleOptionsParent(&$itemOptions = null, $options = null)
    {
        $itemOptions['option_ids'] = implode(',', array_keys($options));

        foreach ($options as $key => $value) {
            $itemOptions['option_' . $key] = $value;
        }
    }

    private function formatBundleOptionIds(&$itemSelections = null, $selections = null)
    {
        $optionIds = [];

        foreach ($selections as $key => $value) {
            $optionIds[] = $key;
        }

        $itemSelections['bundle_option_ids'] = '[' . implode(",", $optionIds) . ']';
    }

    private function formatBundleSelectionsParent(&$itemSelections = null)
    {
        $selectionIds = [];

        foreach ($this->bundleSelectionProductIds as $key => $value) {
            $selectionIds[] = '"' . $key . '"';

            $itemSelections['selection_qty_' . $key] = '1';
            $itemSelections['product_qty_' . $value['product_id']] = '1';
        }

        $itemSelections['bundle_selection_ids'] = '[' . implode(",", $selectionIds)  . ']';
    }

    private function formatBundleSelectionsChild(&$itemSelections = null, $selectionId = null, $selection = null)
    {
        $itemSelections['selection_id'] = $selectionId;

        $itemSelections['bundle_selection_attributes'] = json_encode([
            'price' => 1,
            'qty' => 1,
            'option_label' => $selection['option_title'],
            'option_id' => $selection['option_id']
        ]);
    }

    private function setItemOptions($itemId = 0, $productId = 0, $options = null)
    {
        $itemoption = $this->itemoption;

        foreach ($options as $key => $value) {
                $itemoption->unsetData();
                $itemoption->setItemId($itemId);
                $itemoption->setProductId($productId);
                $itemoption->setCode($key);
                $itemoption->setValue($value);
                $itemoption->save();
        }
    }

    private function addParentItem($bundleProductModel, $childProductModel, $options)
    {
        $selectionPrice = 0;
        $customOptionPrice = 0;
        $customOptionSkus = null;

        if ($bundleProductModel->getId()) {
            $quoteItem = $this->cartItemFactory->create();
            $quoteItem->setProduct($bundleProductModel);

            if ($bundleProductModel->hasOptions() == true) {
                // get custom options
                $productOptions = $bundleProductModel->getOptions();

                foreach ($productOptions as $option) {
                    $values = $option->getValues();

                    foreach ($values as $valueId => $value) {
                        // compare the option values to the selections
                        if ($options[$option['option_id']] == $valueId) {
                            $customOptionPrice += $value->getPrice();

                            if (strlen($value->getSku()) > 0) {
                                $customOptionSkus .= '-' . $value->getSku();
                            }
                        }
                    }
                }
            }

            // iterate through native bundle options
            foreach ($this->bundleSelectionProductIds as $bundle) {
                $selectionPrice += $bundle['price'];
            }

            $price = $bundleProductModel->getPrice() + $childProductModel->getPrice() + $customOptionPrice + $selectionPrice;

            // set the values specific to what they need to be...
            $quoteItem->setQty(1);
            $quoteItem->setProductType('bundle');
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setRowTotal($price);
            $quoteItem->setBaseRowTotal($price);
            $quoteItem->getProduct()->setIsSuperMode(true);

            if ($customOptionSkus != null) {
                $quoteItem->setSku($bundleProductModel->getSku() . $customOptionSkus);
            }

            return $quoteItem;
        }
    }

    private function addChildItem($childProductModel, $parentId = 0, $options)
    {
        $customOptionPrice = 0;
        $customOptionSkus = null;

        if ($childProductModel->getId()) {
            $quoteItem = $this->cartItemFactory->create();
            $quoteItem->setProduct($childProductModel);

            if ($childProductModel->hasOptions() == true) {
                // get custom options
                $productOptions = $childProductModel->getOptions();

                foreach ($productOptions as $option) {
                    $values = $option->getValues();

                    foreach ($values as $valueId => $value) {
                        // compare the option values to the selections
                        if ($options[$option['option_id']] == $valueId) {
                            $customOptionPrice += $value->getPrice();

                            if (strlen($value->getSku()) > 0) {
                                $customOptionSkus .= '-' . $value->getSku();
                            }
                        }
                    }
                }
            }

            // implement the bundle price preference
            if ($childProductModel->getBundlePrice() != null) {
                $price = $childProductModel->getBundlePrice() + $customOptionPrice;
            } else {
                $price = $childProductModel->getPrice() + $customOptionPrice;
            }

            // implement the bundle sku preference
            if (strlen($childProductModel->getBundleSku()) > 0) {
                $quoteItem->setSku($childProductModel->getBundleSku() . $customOptionSkus);
            } else {
                $quoteItem->setSku($childProductModel->getSku() . $customOptionSkus);
            }

            // set the values specific to what they need to be...
            $quoteItem->setParentItemId($parentId);
            $quoteItem->setName($childProductModel->getName());
            $quoteItem->setQty(1);
            $quoteItem->setProductType('simple');
            $quoteItem->setCustomPrice($price);
            $quoteItem->setOriginalCustomPrice($price);
            $quoteItem->setRowTotal($price);
            $quoteItem->setBaseRowTotal($price);
            //$quoteItem->getProduct()->setIsSuperMode(true);

            return $quoteItem;
        }
    }

    /**
     * get all the selection products used in bundle product
     * @param $product
     * @return mixed
     */
    private function getBundleOptions($product)
    {
        $selectionCollection = $product->getTypeInstance()
            ->getSelectionsCollection(
                $product->getTypeInstance()->getOptionsIds($product),
                $product
            );
        $bundleOptions = [];
        foreach ($selectionCollection as $selection) {
            $bundleOptions[$selection->getOptionId()][] = $selection->getSelectionId();
        }
        return $bundleOptions;
    }

    /**
     * get the unique identifer used in cart/quote
     * @param $product
     * @return mixed
     */
    private function getBundleIdentity(\Magento\Catalog\Model\Product $product = null, $childId = 0, $bundleProductSelections)
    {
        $bundleSelectionProductIds = [];
        $bundleSelectionsId = [];

        // get bundled options
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);

        foreach ($optionsCollection as $option) {

            if ($option->getIsDynamicSelection() == 1) {
                $this->bundleDynamicOptionIds[] = $option->getOptionId();
            }

            // handle native bundle
            $selections = $product->getTypeInstance(true)
                ->getSelectionsCollection($option->getOptionId(), $product);


            foreach ($selections as $selection) {
                if ($bundleProductSelections[$option->getId()] == $selection->getSelectionId()) {
                    $bundleSelectionsId[] = $selection->getSelectionId();

                    // native selection mapping
                    $bundleSelectionProductIds[$selection->getSelectionId()] = [
                        'product_id' => $selection->getProductId(),
                        'price' => $selection->getPrice(),
                        'option_id' => $option->getOptionId(),
                        'option_title' => $option->getTitle()
                    ];

                    break;
                }
            }
        }

        // format identifier string
        $this->bundleIdentity = $product->getId() . "_" . implode("_1_", $bundleSelectionsId) . "_1";

        // used by other functions to map products into cart
        $this->bundleSelectionProductIds = $bundleSelectionProductIds;
    }

    private function formatBundleOptionSelection()
    {
        $bundleOptions = [];

        foreach ($this->bundleSelectionProductIds as $selectionId => $bundeOption) {
            $bundleOptions[$bundeOption['option_id']] = "{$selectionId}";
        }

        return $bundleOptions;
    }
}
