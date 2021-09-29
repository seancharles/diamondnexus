<?php

namespace ForeverCompanies\Profile\Controller\RingBuild;

use Magento\Framework\Event\ManagerInterface as EventManager;

    /**
     * Controller for processing add set to cart action.
     *
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
class AddSet extends \ForeverCompanies\Profile\Controller\ApiController
{
    protected $profileHelper;
    protected $resultHelper;
    protected $eventManager;
        
   /**
    * @param \Magento\Framework\App\Action\Context $context
    * @param \ForeverCompanies\Profile\Helper\Profile $profileHelper
    * @param \ForeverCompanies\Profile\Helper\Result $resultHelper
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \ForeverCompanies\Profile\Helper\Profile $profileHelper,
        \ForeverCompanies\Profile\Helper\Result $resultHelper,
        EventManager $eventM
    ) {
         
        $this->productloader = $productloader;
        $this->profileHelper = $profileHelper;
        $this->resultHelper = $resultHelper;
        $this->eventManager = $eventM;
            
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $this->profileHelper->getPost();
                
            if ($this->profileHelper->formKeyValidator->validate($this->getRequest())) {
                    
                 $settingParams = $this->profileHelper->getProfileSessionKey('set_setting');
                 $stoneParams = $this->profileHelper->getProfileSessionKey('set_stone');
                    
                if (isset($settingParams['product']) == false) {
                    $this->resultHelper->addProductError(0, "Invalid setting product id.");
                }
                    
                if (isset($stoneParams['product']) == false) {
                    $this->resultHelper->addProductError(0, "Invalid stone product id.");
                }
                    
                 $errorResult = $this->resultHelper->getErrors();

                if (count($errorResult['configurable_option']) == 0 && count($errorResult['custom_option']) == 0) {

                    $setId = time();

                    $settingId = (int) $settingParams['product'];
                    $stoneId = (int) $stoneParams['product'];
                        
                    $settingProduct = $this->productloader->create()->load($settingId);

                    $this->profileHelper->addCartItem($stoneId, $stoneParams, $setId);
                    $this->profileHelper->addCartItem($settingId, $settingParams, $setId);

                    $this->profileHelper->cart->save();

                    $this->profileHelper->setProfileSessionKey('set_type', null);
                    $this->profileHelper->setProfileSessionKey('set_setting', null);
                    $this->profileHelper->setProfileSessionKey('set_setting_sku', null);
                    $this->profileHelper->setProfileSessionKey('set_stone', null);
                    $this->profileHelper->setProfileSessionKey('set_stone_sku', null);

                    // update the current profile instance
                    $this->profileHelper->setProfileKey('set_builder', [
                        'type' => null,
                        'setting' => null,
                        'setting_sku' => null,
                        'stone' => null,
                        'stone_sku' => null
                    ]);

                    $message = __(
                        'Added %1 to set to cart',
                        $settingProduct->getName()
                    );
                        
                    $this->resultHelper->addSuccessMessage($message);
                    $this->resultHelper->setSuccess(true);
                        
                    // updates the last sync time
                    $this->profileHelper->sync();
                    
                    $this->resultHelper->setProfile(
                        $this->profileHelper->getProfile()
                    );
                        
                }
            } else {
                $this->resultHelper->addFormKeyError();
            }
        } catch (\Exception $e) {
            $this->resultHelper->addExceptionError($e);
        }
            
        $this->eventManager->dispatch('free_gift_add_logic');
        $this->resultHelper->getResult();
    }
}
