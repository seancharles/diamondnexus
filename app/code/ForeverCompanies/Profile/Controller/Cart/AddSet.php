<?php

    namespace ForeverCompanies\Profile\Controller\Cart;

    use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
    use Magento\Checkout\Model\Cart as CustomerCart;
    use Magento\Framework\App\ResponseInterface;
    use Magento\Framework\Controller\ResultInterface;
    use Magento\Framework\Exception\NoSuchEntityException;

	class AddSet extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
	{
		protected $profileHelper;
		protected $resultHelper;
		
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Checkout\Model\Session $checkoutSession,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
            CustomerCart $cart,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper,
            \Magento\Framework\DataObject\Factory $dataObjectFactory
        ) {
            parent::__construct(
                $context,
                $scopeConfig,
                $checkoutSession,
                $storeManager,
                $formKeyValidator,
                $cart
            );
            $this->productRepository = $productRepository;
            $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
            $this->dataObjectFactory = $dataObjectFactory;
        }

		public function execute()
		{
			try{
                $settingParams = $this->profileHelper->getProfileSessionKey('set_setting');
                $stoneParams = $this->profileHelper->getProfileSessionKey('set_stone');
                
                if(isset($settingParams['product']) == false) {
                    $this->resultHelper->addProductError(0, "Invalid setting product id.");
                }
                
                if(isset($stoneParams['product']) == false) {
                    $this->resultHelper->addProductError(0, "Invalid stone product id.");
                }
                
                $errorResult = $this->resultHelper->getErrors();

                if(count($errorResult['configurable_option']) == 0 && count($errorResult['custom_option']) == 0) {

                    $quote = $this->profileHelper->getQuote();

                    $setId = time();
                    
                    $this->_checkoutSession->setBundleIdentifier($setId);
                    
                    $storeId = $this->_objectManager->get(
                        \Magento\Store\Model\StoreManagerInterface::class
                    )->getStore()->getId();
                    
                    $settingProduct = $this->productRepository->getById($settingParams['product'], false, $storeId);
                    
                    $result = $quote->addProduct($settingProduct, $this->dataObjectFactory->create($settingParams));
                    $this->profileHelper->saveQuote($quote);
                    
                    $this->_eventManager->dispatch(
                        'checkout_cart_product_add_after',
                        ['quote_item' => $result, 'product' => $settingProduct]
                    );
                    
                    $product = $this->productRepository->getById($stoneParams['product'], false, $storeId);
                    
                    $result = $quote->addProduct($product, $this->dataObjectFactory->create($stoneParams));
                    $this->profileHelper->saveQuote($quote);
                    
                    $this->_eventManager->dispatch(
                        'checkout_cart_product_add_after',
                        ['quote_item' => $result, 'product' => $product]
                    );
                    
                    // clear values
                    $this->_checkoutSession->setBundleIdentifier(null);
                    
                    $this->profileHelper->setProfileSessionKey('set_type', null);
                    $this->profileHelper->setProfileSessionKey('set_setting', null);
                    $this->profileHelper->setProfileSessionKey('set_setting_sku', null);
                    $this->profileHelper->setProfileSessionKey('set_stone', null);
                    $this->profileHelper->setProfileSessionKey('set_stone_sku', null);

                    // update the current profile instance
                    $this->profileHelper->setProfileKey('set_builder', [
                        'type' => null,
                        'setting' => null,
                        'setting_sku' => null,
                        'stone' => null,
                        'stone_sku' => null
                    ]);

                    $message = __(
                        'Added %1 to set to cart',
                        $settingProduct->getName()
                    );
                    
                    $this->resultHelper->addSuccessMessage($message);
                    $this->resultHelper->setSuccess(true);
                    
                    // updates the last sync time
                    $this->profileHelper->sync();
                
                    $this->resultHelper->setProfile(
                        $this->profileHelper->getProfile()
                    );
                    
                }
			
			} catch (\Exception $e) {
				$this->resultHelper->addExceptionError($e);
			}
			
			$this->resultHelper->getResult();
		}
	}