<?php

namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BuildFeedsEarly
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
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/feed/build_feeds_early', $this->storeScope)) {
            return $this;
        }

        // TODO Schedules are currently hard-coded but can be moved into config at a later date.
        $this->feedModel->BuildCsvs(1);
        $this->feedModel->BuildCsvs(12);
        return;
    }
}
