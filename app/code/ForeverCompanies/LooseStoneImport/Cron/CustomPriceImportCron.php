<?php

namespace ForeverCompanies\LooseStoneImport\Cron;

use ForeverCompanies\LooseStoneImport\Model\StoneCustomPriceImport;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CustomPriceImportCron
{
    protected StoneCustomPriceImport $stoneCustomPriceImportModel;
    protected ScopeConfigInterface $scopeConfig;
    protected string $storeScope;

    public function __construct(
        StoneCustomPriceImport $stoneCustomPriceImport,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->stoneCustomPriceImportModel = $stoneCustomPriceImport;
        $this->scopeConfig = $scopeConfigInterface;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/loose_stone/custom_price_import', $this->storeScope)) {
            return $this;
        }

        $this->stoneCustomPriceImportModel->run();
        return;
    }
}