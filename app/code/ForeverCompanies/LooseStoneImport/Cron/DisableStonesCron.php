<?php

namespace ForeverCompanies\LooseStoneImport\Cron;

use ForeverCompanies\LooseStoneImport\Model\StoneDisable;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DisableStonesCron
{
    protected $stoneDisableModel;
    protected $scopeConfig;
    protected $storeScope;

    public function __construct(
        StoneDisable $stone,
        ScopeConfigInterface $scopeC
    ) {
        $this->stoneDisableModel = $stone;
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    public function execute() 
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/loose_stone/disable_stones', $this->storeScope)) {
            return $this;
        }
        
        $this->stoneDisableModel->run();
        return;
    }
}