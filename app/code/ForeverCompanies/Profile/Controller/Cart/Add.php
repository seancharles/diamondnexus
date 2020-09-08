<?php
    /**
     * Add simple and configurable products to the cart
     */
	namespace ForeverCompanies\Profile\Controller\Cart;

	class Add extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $productloader;
		protected $profileHelper;
		protected $simpleHelper;
		protected $dataObjectFactory;
		
		public function __construct(
			\Magento\Catalog\Model\ProductFactory $productloader,
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Product\Simple $simpleHelper,
			\Magento\Framework\DataObject\Factory $dataObjectFactory,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->productloader = $productloader;
			$this->profileHelper = $profileHelper;
			$this->simpleHelper = $simpleHelper;
			$this->dataObjectFactory = $dataObjectFactory;
			
			parent::__construct($context);
		}

		public function execute()
		{
			$result = [
				'success' => false
			];
			
			try{
				$post = $this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

					$productId = $post->product;
					$qty = $post->qty;

					// get the quote id
					$quote = $this->profileHelper->getQuote();
					
					if($productId > 0) {
						$productModel = $this->productloader->create()->load($productId);

						$params = array(
							'product' => $productId,
							'qty' => $qty
						);
						
						if(isset($post->super_attributes) == true) {
							// convert configurable options to array
							foreach($post->super_attributes as $attribute) {
								$params['super_attribute'][$attribute->id] = $attribute->value;
							}
						}
						
						if(isset($post->options) == true) {
							// convert custom options to array
							foreach($post->options as $option) {
								$params['options'][$option->id] = $option->value;
							}
						}

						$quote->addProduct($productModel,$this->dataObjectFactory->create($params));
						
						$this->profileHelper->saveQuote();
						
						$message = __(
							'You added %1 to your shopping cart.',
							$productModel->getName()
						);
						
						$result['success'] = true;
						$result['message'] = $message;
						
						// updates the last sync time
						$this->profileHelper->sync();
						
						$result['profile'] = $this->profileHelper->getProfile();
					}
					
				} else {
					$result['success'] = false;
					$result['message'] = 'Invalid form key.';
				}
			
			} catch (\Exception $e) {
				$result['message'] = $e->getMessage();
			}
			
			print_r(json_encode($result));
		}
	}