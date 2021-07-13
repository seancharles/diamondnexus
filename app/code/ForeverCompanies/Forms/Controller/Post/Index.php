<?php

namespace ForeverCompanies\Forms\Controller\Post;

use ForeverCompanies\Forms\Model\DataExampleFactory;
use ForeverCompanies\Forms\Model\IterableClass;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;


// include_once("IterableClass.php");

class Index extends \ForeverCompanies\Forms\Controller\ApiController
{
	protected $formKeyValidator;
	protected $storeManager;
    protected $cookieManager;
	protected $submissionFactory;
	protected $formHelper;
	protected $iterableModel;
    
    const COOKIE_NAME = 'submission_key';
    const COOKIE_DURATION = 86400; // lifetime in seconds
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
		\ForeverCompanies\Forms\Model\SubmissionFactory  $submissionFactory,
		\ForeverCompanies\Forms\Helper\Form $formHelper,
	    IterableClass $iterableC
	) {
		parent::__construct($context);
		
		$this->formKeyValidator = $formKeyValidator;
		$this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
		$this->submissionFactory = $submissionFactory;
		$this->formHelper = $formHelper;
		$this->iterableModel = $iterableC;
	}

    protected function setCompatibleFormKey()
    {
        // parse the json post
        $json = file_get_contents('php://input');

        // Converts it into a PHP object
        $data = json_decode($json);

        if(isset($data->form_key) == true) {
            // get form key
            $formKey = $data->form_key;
            
            // translate ajax post object to form value to validate
            $this->request->setPostValue('form_key', $formKey);
        }
    }

	public function execute()
	{
        $this->setCompatibleFormKey();
        
		if ($this->formKeyValidator->validate($this->getRequest()) !== false) {
	
		    // if field is populated a bot filled out the honey pot.
		    if (trim($this->formHelper->getSanitizedField('email_confirm')) == "") {
		        
		        $websiteId = $this->storeManager->getWebsite()->getId();
		        $formId = $this->formHelper->getSanitizedField('form_id');
		        $email = $this->formHelper->getSanitizedField('email');
		        $formData = json_encode($this->getRequest()->getParams());
		        
		        $model = $this->submissionFactory->create();
		        
		        $leadKey = $this->cookieManager->getCookie(self::COOKIE_NAME);
		        
		        if(!strlen($leadKey) > 0) {
		            // generate new submission key (unique to each form)
		            $leadKey = $websiteId . $formId . bin2hex(random_bytes(20));
		            
		            $metadata = $this->cookieMetadataFactory
		            ->createPublicCookieMetadata()
		            ->setDuration(self::COOKIE_DURATION);
		            
		            $this->cookieManager->setPublicCookie(
		                self::COOKIE_NAME,
		                $leadKey,
		                $metadata
		                );
		        }
		        
		        $model->addData([
		            "lead_key" => $leadKey,
		            "form_id" => $formId,
		            "website_id" => $websiteId,
		            "email" => $email,
		            "form_post_json" => $formData,
		            "created_at" => date("Y-m-d h:i:s", time())
		        ]);
		        
		        // track event for TF Drop a Hint email.
		        if ($formId == 15) {
		            
	                $iterable = new IterableClass('98ae3d33e12843f3b2cfbeb4e27ea0ad');
	                
	                $friendsEmail = $this->formHelper->getSanitizedField('friendemail');
	                $message = $this->formHelper->getSanitizedField('message');
	                $name = $this->formHelper->getSanitizedField('name');
	                $copy = $this->formHelper->getSanitizedField('copy');
	                $updates = $this->formHelper->getSanitizedField('updates');
            	   
            	    $iterable->event_track(
            	        $email,
            	        'tf_drop_hint',
            	        time(),
            	        array(
            	            'friendemail' => $friendsEmail,
            	            'message' => $message,
            	            'name' => $name,
            	            'copy' => $copy,
            	            'updates' => $updates
            	        ),
            	        false,
            	        false
            	    );		            
		        }
		        
		        $saveData = $model->save();
		    }
		    
			echo json_encode(['success'=>true]);
			
		} else {
			echo json_encode([
				'success'=>false,
				'message'=> 'Invalid Form Key'
			]);
		}
	}
}