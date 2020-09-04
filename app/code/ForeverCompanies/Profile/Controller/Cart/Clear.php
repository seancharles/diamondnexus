<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class Clear extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $profileHelper;
		protected $cartHelper;
		
		public function __construct(
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Quote $cartHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->profileHelper = $profileHelper;
			$this->quoteHelper = $cartHelper;
			parent::__construct($context);
		}

		public function execute()
		{
			$result = [
				'success' => false
			];
			
			try {
				$post = $this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
					// clear cart contents
					$this->quoteHelper->clear();
					
					// updates the last sync time
					$this->profileHelper->sync();
					
					$result['success'] = true;
					$result['message'] = 'Cart cleared.';
					$result['profile'] = $this->profileHelper->getProfile();
					
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