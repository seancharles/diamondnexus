<?php

	namespace ForeverCompanies\Profile\Controller\Sync;

	class Index extends \Magento\Framework\App\Action\Action
	{
		public function __construct(
			\ForeverCompanies\Profile\Helper\Profile $helper,
			\Magento\Backend\App\Action\Context $context
		) {
			$this->profileHelper = $helper;
			parent::__construct($context);
		}

		public function execute()
		{
			// updates the last sync time
			$this->profileHelper->sync();
			
			$profile = $this->profileHelper->getProfile();
			
			print_r($profile);
			exit;
		}
	}