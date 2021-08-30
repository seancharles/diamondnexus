<?php

namespace ForeverCompanies\CustomSales\Plugin;

use ForeverCompanies\CustomSales\Helper\Producer;

class StagingApplier
{
    protected $producerHelper;

    public function __construct(
        Producer $producerHelper
    ) {
        $this->producerHelper = $producerHelper;
    }

    public function afterExecute($subject, $result)
    {
        $this->producerHelper->exportElder();

        return $result;
    }
}
