<?php

	namespace ForeverCompanies\Profile\Controller\Cart;

	class Clear extends \ForeverCompanies\Profile\Controller\ApiController
	{
		protected $profileHelper;
		protected $resultHelper;
		
		public function __construct(
			\ForeverCompanies\Profile\Helper\Profile $profileHelper,
			\ForeverCompanies\Profile\Helper\Result $resultHelper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->profileHelper = $profileHelper;
			$this->resultHelper = $resultHelper;
			parent::__construct($context);
		}

		public function execute()
		{
			try {
				$post = $this->profileHelper->getPost();
				
				if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
					// clear cart contents
					$this->profileHelper->clearQuote();
					
					// updates the last sync time
					$this->profileHelper->sync();
					
					$this->resultHelper->setSuccess(true, "Cart cleared.");
					
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
	}