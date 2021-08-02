<?php

    namespace ForeverCompanies\Profile\Controller\Cart;

class Remove extends \ForeverCompanies\Profile\Controller\ApiController
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
                
            $itemsList = [];
                
            if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
                    
                $itemPost = $this->profileHelper->getPostParam('item_list');
                    
                // convert post object to array;
                foreach ($itemPost as $item) {
                    $itemsList[$item] = $item;
                }
                    
                if (count($itemsList) > 0) {
                    // get the cart items
                    $quoteItems = $this->profileHelper->getCartItems();
                        
                    // iterate the users cart items
                    foreach ($quoteItems as $item) {
                        if (in_array($item->getItemId(), $itemsList) == true) {
                            $item->delete();
                        }
                    }
                        
                    $this->resultHelper->setSuccess(true, 'Removed item(s) from cart');
                        
                } else {
                    $this->resultHelper->addCartError("Unable to find cart item");
                }
                    
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
