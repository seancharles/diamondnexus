<?php

namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;

class CreateReviews
{
    protected $feedModel;

    public function __construct(
        FeedLogic $feed
        ) {
        $this->feedModel = $feed;
    }

    public function execute() 
    {
        $this->feedModel->updateReviews(1);
    }
}