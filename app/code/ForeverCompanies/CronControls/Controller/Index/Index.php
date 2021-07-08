<?php

namespace ForeverCompanies\CronControls\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $scopeConfig;
    protected $storeScope;
    
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeC
    ) { 
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        
        return parent::__construct($context);
    }
    
    public function execute()
    {
        if ($this->scopeConfig->getValue('forevercompanies_cron_controls/loose_stone/loose_stone_import', $this->storeScope)) {
            echo 'yes';
        } else {
            echo 'no';
        }
    }
}