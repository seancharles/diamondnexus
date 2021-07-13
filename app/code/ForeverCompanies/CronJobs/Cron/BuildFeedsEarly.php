<?php
namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;

class BuildFeedsarly
{
    protected $feedModel;

    public function __construct(
        FeedLogic $feed
    ) {
        $this->feedModel = $feed;
    }

    public function execute()
    {
        // TODO Schedules are currently hard-coded but can be moved into config at a later date.
        $this->feedModel->BuildCsvs(1);
        $this->feedModel->BuildCsvs(12);
        return;
    }
}
