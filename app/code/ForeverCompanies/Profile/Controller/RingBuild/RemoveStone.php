<?php

    namespace ForeverCompanies\Profile\Controller\RingBuild;

class RemoveStone extends \ForeverCompanies\Profile\Controller\ApiController
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
            $this->profileHelper->getPost();
                
            if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {

                // values are stored in checkout session
                $this->profileHelper->setProfileSessionKey('set_stone', null);
                $this->profileHelper->setProfileSessionKey('set_stone_sku', null);

                // update the current profile instance
                $this->profileHelper->setProfileBuilderKey('stone', null);
                $this->profileHelper->setProfileBuilderKey('stone_sku', null);
                    
                $this->resultHelper->addSuccessMessage('Stone was removed from session.');
                $this->resultHelper->setSuccess(true);
                    
                // updates the last sync time
                $this->profileHelper->sync();
                
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
