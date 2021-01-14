<?php

	namespace ForeverCompanies\Forms\Controller\Post;

	use ForeverCompanies\Forms\Model\DataExampleFactory;
	use Magento\Framework\Controller\ResultFactory;
	use Magento\Framework\App\Action\Context;

	class Index extends \ForeverCompanies\Forms\Controller\ApiController
	{
		protected $formKeyValidator;
		protected $storeManager;
		protected $submissionFactory;
		protected $formHelper;
		
		public function __construct(
			\Magento\Framework\App\Action\Context $context,
			\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
			\Magento\Store\Model\StoreManagerInterface $storeManager,
			\ForeverCompanies\Forms\Model\SubmissionFactory  $submissionFactory,
			\ForeverCompanies\Forms\Helper\Form $formHelper
		) {
			parent::__construct($context);
			
			$this->formKeyValidator = $formKeyValidator;
			$this->storeManager = $storeManager;
			$this->submissionFactory = $submissionFactory;
			$this->formHelper = $formHelper;
		}

		public function execute()
		{
			if ($this->formKeyValidator->validate($this->getRequest()) == false) {
			
				$storeId = $this->storeManager->getStore()->getId();
				$formId = $this->formHelper->getSanitizedField('form_id');
				
				$model = $this->submissionFactory->create();
				
				$model->addData([
					"store_id" => $storeId,
					"form_id" => $formId,
					"form_post_json" => json_encode(['key'=>'hello world']),
					"created_at" => date("Y-m-d h:i:s", time())
				]);
				
				$saveData = $model->save();
				
				echo json_encode(['success'=>true]);
				
			} else {
				echo json_encode([
					'success'=>false,
					'message'=> 'Invalid Form Key'
				]);
			}
		}
	}