<?php

	namespace ForeverCompanies\Forms\Controller\Post;

	class Index extends \Magento\Framework\App\Action\Action
	{
		public function __construct(
			\Magento\Backend\App\Action\Context $context
		) {
			parent::__construct($context);
		}

		public function execute()
		{
			echo "Hello World";
		}
	}