<?php

namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;

class BuildFeedsLate
{
    protected $feedModel;

    public function __construct(
        FeedLogic $feed
    ) {
        $this->feedModel = $feed;
    }

    public function execute() 
    {
        $this->feedModel->BuildCsvs(1);
        return;
    }
}