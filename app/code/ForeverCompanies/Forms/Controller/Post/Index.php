<?php

	namespace ForeverCompanies\Forms\Controller\Post;

	use ForeverCompanies\Forms\Model\DataExampleFactory;
	use Magento\Framework\Controller\ResultFactory;
	use Magento\Framework\App\Action\Context;

	class Index extends \Magento\Framework\App\Action\Action
	{
		protected $submissionFactory;
		protected $resultFactory;
		
		public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\ForeverCompanies\Forms\Model\SubmissionFactory  $submissionFactory,
			\Magento\Framework\Controller\ResultFactory $resultFactory
		) {
			parent::__construct($context);
			
			$this->submissionFactory = $submissionFactory;
			$this->resultFactory = $resultFactory;
		}

		public function execute()
		{
			echo "Hello World";
			
			$model = $this->submissionFactory->create();
			$model->addData([
				"store_id" => '1',
				"form_post_json" => json_encode(['key'=>'hello world']),
				"created_at" => date("Y-m-d h:i:s", time())
			]);
			$saveData = $model->save();
		}
	}