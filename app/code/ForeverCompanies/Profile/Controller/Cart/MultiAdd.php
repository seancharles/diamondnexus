<?php
    /**
     * Add simple and configurable products to the cart
     */
	namespace ForeverCompanies\Profile\Controller\Cart;

	class MultiAdd extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $productloader;
		protected $profileHelper;
		protected $resultHelper;
		
		public function __construct(
			\Magento\Catalog\Model\ProductFactory $productloader,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->productloader = $productloader;
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
			
			parent::__construct($context);
		}

		public function execute()
		{
			try{
				$this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

					$productId = $this->profileHelper->getPostParam('product');
					$qty = $this->profileHelper->getPostParam('qty');
					
					$superAttributes = $this->profileHelper->getPostParam('super_attributes');
					$options = $this->profileHelper->getPostParam('options');

					if($productId > 0) {
						
						$productModel = $this->productloader->create()->load($productId);
						// valid product loaded
						
						if(isset($productModel) == true && $productModel->getId() > 0) {
							
							$params = array(
								'product' => $productId,
								'qty' => $qty
							);
							
							if(isset($superAttributes) == true) {
								// convert configurable options to array
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
								
								$this->resultHelper->setSuccess(true, $message);
								
								// updates the last sync time
								$this->profileHelper->sync();
							
								$this->resultHelper->setProfile(
									$this->profileHelper->getProfile()
								);
							} else {
								foreach($validationResult as $error) {
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