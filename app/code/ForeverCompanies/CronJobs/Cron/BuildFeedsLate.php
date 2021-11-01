<?php

namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BuildFeedsLate
{
    protected FeedLogic $feedModel;
    protected ScopeConfigInterface $scopeConfig;
    protected string $storeScope;

    public function __construct(
        FeedLogic $feed,
        ScopeConfigInterface $scopeC
    ) {
        $this->feedModel = $feed;
        $this->scopeConfig = $scopeC;
        $this->storeScope = ScopeInterface::SCOPE_STORE;
    }

    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/feed/build_feeds_late', $this->storeScope)) {
            return $this;
        }
        
        $this->feedModel->BuildCsvs(1);
        $this->feedModel->BuildCsvs(12);
        return;
    }
}
