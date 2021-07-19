<?php

namespace ForeverCompanies\LooseStoneImport\Cron;

use ForeverCompanies\LooseStoneImport\Model\StoneImport;
use Magento\Framework\App\Config\ScopeConfigInterface;

class LooseStoneImport
{
    protected $stoneImportModel;
    protected $scopeConfig;
    protected $storeScope;

    public function __construct(
        StoneImport $stone,
        ScopeConfigInterface $scopeC
    ) {
        $this->stoneImportModel = $stone;
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    public function execute() 
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/loose_stone/loose_stone_import', $this->storeScope)) {
            return $this;
        }
        
        $this->stoneImportModel->run();
        return;
    }
}