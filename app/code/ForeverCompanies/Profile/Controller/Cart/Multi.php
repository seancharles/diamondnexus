<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

    /**
     * Controller for processing multi add to cart action.
     *
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
	class Multi extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $productloader;
		protected $profileHelper;
		protected $resultHelper;
        
        /**
         * @param \Magento\Framework\App\Action\Context $context
         * @param \ForeverCompanies\Profile\Helper\Profile $profileHelper
         * @param \ForeverCompanies\Profile\Helper\Result $resultHelper
         */
        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            \Magento\Catalog\Model\ProductFactory $productloader,
            \ForeverCompanies\Profile\Helper\Profile $profileHelper,
            \ForeverCompanies\Profile\Helper\Result $resultHelper
        ) {
            parent::__construct($context);
            $this->productloader = $productloader;
            $this->profileHelper = $profileHelper;
            $this->resultHelper = $resultHelper;
        }

		public function execute()
		{
            $hasErrors = false;
            
			try{
				$this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

                    $productList = $this->profileHelper->getPostParam('products');

                    if(isset($productList) == true) {
                        
                        foreach($productList as $product) {
                                
                            if(isset($product) == true) {
                                
                                $productId = (isset($product->product) == true) ? $product->product : 0;
                                $qty = (isset($product->qty) == true) ? $product->qty : 0;
                                
                                $superAttributes = (isset($product->super_attributes) == true) ? (array) $product->super_attributes : [];
                                $options = (isset($product->options) == true) ? (array) $product->options : 0;
                                
                                if($productId > 0) {
                                    
                                    $productModel = $this->productloader->create()->load($productId);
                                    
                                    // valid product loaded
                                    if(isset($productModel) == true && $productModel->getId() > 0) {
                                        
                                        $params = array(
                                            'product' => $productId,
                                            'qty' => $qty
                                        );
                                        
                                        if(isset($superAttributes) == true) {
                                            foreach($superAttributes as $attribute) {
                                                $params['super_attribute'][$attribute->id] = $attribute->value;
                                            }
                                        }
                                        
                                        if(isset($options) == true) {
                                            // convert custom options to array
                                            foreach($options as $option) {
                                                $params['options'][$option->id] = $option->value;
                                            }
                                        }
                                        
                                        $validationResult = $this->resultHelper->validateProductOptions($productModel, $params);

                                        if($validationResult == false) {

                                            $this->profileHelper->addCartItem($productId, $params);
                                            
                                            $message = __(
                                                'You added %1 to your shopping cart.',
                                                $productModel->getName()
                                            );
                                            
                                            $this->resultHelper->addSuccessMessage($message);
                                            $this->resultHelper->setSuccess(true);
                                            
                                            // updates the last sync time
                                            $this->profileHelper->sync();
                                        
                                            $this->resultHelper->setProfile(
                                                $this->profileHelper->getProfile()
                                            );
                                            
                                        } else {
                                            foreach($validationResult as $error) {
                                                $hasErrors = true;
                                                $this->resultHelper->addProductError($productId, $error);
                                            }
                                        }
                                        
                                    } else {
                                        $this->resultHelper->addProductError($productId, "Product could not be found.");
                                    }
                                } else {
                                    $this->resultHelper->addProductError($productId, "Product ID is invalid.");
                                }
                            }
                        }
                    }
				} else {
					$this->resultHelper->addFormKeyError();
				}
			} catch (\Exception $e) {
				$this->resultHelper->addExceptionError($e);
			}
			
			$this->resultHelper->getResult();
		}
	}