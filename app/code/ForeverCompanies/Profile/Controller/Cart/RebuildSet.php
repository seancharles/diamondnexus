<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class RebuildSet extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $profileHelper;
		protected $resultHelper;
        
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
            \Magento\Checkout\Model\Cart $cart,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper
        ) {
            $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
            $this->cart = $cart;
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
            
            parent::__construct($context);
        }
        
		public function execute()
		{
			try{
                $this->profileHelper->getPost(
                    \ForeverCompanies\Profile\Helper\Profile::POST_TYPE_ARRAY,
                    $this->getRequest()->getPostValue()
                );

                if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
                    
                    $itemId = $this->profileHelper->getPostParam('itemid');
                    $setId = $this->profileHelper->getPostParam('setid');
                    
                    $quoteId = $this->cart->getQuote()->getId();
                    
                    $collection = $this->quoteItemCollectionFactory->create();
                    $collection->addFieldToFilter('quote_id', $quoteId);
                    $collection->addFieldToFilter('set_id', $setId);
                    
                    if($collection) {
                        foreach($collection as $item) {
                            $option = $item->getOptionByCode('info_buyRequest');
                            
                            if($option) {
                                $optionArray = (array) json_decode($option->getValue());
                                
                                // check for the item type and set it to the ring builder session component
                                if($item->getProduct()->getAttributeSetId() == 18 || $item->getProduct()->getAttributeSetId() == 32) {
                                    // values are stored in checkout session
                                    $this->profileHelper->setProfileSessionKey('set_type', 'ring');
                                    $this->profileHelper->setProfileSessionKey('set_setting', $optionArray);
                                    $this->profileHelper->setProfileSessionKey('set_setting_sku', $item->getProduct()->getSku());

                                    // update the current profile instance
                                    $this->profileHelper->setProfileBuilderKey('type', 'ring');
                                    $this->profileHelper->setProfileBuilderKey('setting', $optionArray);
                                    $this->profileHelper->setProfileBuilderKey('setting_sku', $item->getProduct()->getSku());
                                    
                                }elseif($item->getProduct()->getAttributeSetId() == 31) {
                                    // values are stored in checkout session
                                    $this->profileHelper->setProfileSessionKey('set_stone', $optionArray);
                                    $this->profileHelper->setProfileSessionKey('set_stone_sku', $item->getProduct()->getSku());

                                    // update the current profile instance
                                    $this->profileHelper->setProfileBuilderKey('stone', $optionArray);
                                    $this->profileHelper->setProfileBuilderKey('stone_sku', $item->getProduct()->getSku());
                                }
                            }
                            
                            // remove the item from cart
                            $item->delete();
                        }
                    }
                    
                    /*
                    $message = __(
                        'Added %1 to set',
                        $productModel->getName()
                    );
                    */
                    
                    $this->resultHelper->addSuccessMessage('complete');
                    $this->resultHelper->setSuccess(true);
                    
                    // updates the last sync time
                    $this->profileHelper->sync();
                
                    $this->resultHelper->setProfile(
                        $this->profileHelper->getProfile()
                    );
                    
				} else {
					$this->resultHelper->addFormKeyError();
				}
			
			} catch (\Exception $e) {
				$this->resultHelper->addExceptionError($e);
			}
            
			$this->resultHelper->getResult();
		}
        
        public function getSelectedOptions($item){
         $result = [];
            $options = $item->getProductOptions();
            if ($options) {
                if (isset($options['options'])) {
                    $result = array_merge($result, $options['options']);
                }
                if (isset($options['additional_options'])) {
                    $result = array_merge($result, $options['additional_options']);
                }
                if (isset($options['attributes_info'])) {
                    $result = array_merge($result, $options['attributes_info']);
                }
            }
            return $result;
        }
	}
