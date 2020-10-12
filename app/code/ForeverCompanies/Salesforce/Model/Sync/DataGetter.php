<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;


class DataGetter
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * DataGetter constructor.
     *
     * @param Job $job
     */
    public function __construct(
        Job $job
    ){
        $this->job = $job;
    }

    /**
     * Return an array of pricebook entries on Salesforce
     * @return mixed|string
     */
    public function getAllSalesforceOrders()
    {
        $query = "SELECT Id, OrderNumber FROM Order";
        $result = $this->job->sendBatchRequest('query', 'Order', $query);
        return $result;
    }
}
