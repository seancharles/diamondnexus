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
				// clear cart contents
				$this->quoteHelper->clear();
				
				// updates the last sync time
				$this->profileHelper->sync();
				
				$result['success'] = true;
				$result['profile'] = $this->profileHelper->getProfile();
			
			} catch (\Exception $e) {
				$result['message'] = $e->getMessage();
			}
			
			print_r(json_encode($result));
		}
	}