<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class AddStone extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $profileHelper;
		protected $resultHelper;
		
		public function __construct(
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper,
			\Magento\Backend\App\Action\Context $context
		) {
            $this->productRepository = $productRepository;
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
			parent::__construct($context);
		}

		public function execute()
		{
            $hasErrors = false;
            
			try{
				$this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

                    $productId = $this->profileHelper->getPostParam('product');

                    if($productId > 0) {
                        
                        $storeId = $this->_objectManager->get(
                            \Magento\Store\Model\StoreManagerInterface::class
                        )->getStore()->getId();
                        
                        $productModel = $this->productRepository->getById($productId, false, $storeId);
                        
                        if(isset($productModel) == true && $productModel->getId() > 0) {
                            
                            $params = array(
                                'product' => $productId
                            );
                            
                            $validationResult = $this->resultHelper->validateProductOptions($productModel, $params);

                            if($validationResult == false) {

                                // values are stored in checkout session
                                $this->profileHelper->setProfileSessionKey('set_type','ring');
                                $this->profileHelper->setProfileSessionKey('set_stone',$params);

                                // update the current profile instance
                                $this->profileHelper->setProfileBuilderKey('type', 'ring');
                                $this->profileHelper->setProfileBuilderKey('stone', $params);

                                $message = __(
                                    'Added %1 to set',
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
				} else {
					$this->resultHelper->addFormKeyError();
				}
			
			} catch (\Exception $e) {
				$this->resultHelper->addExceptionError($e);
			}
			
			$this->resultHelper->getResult();
		}
	}