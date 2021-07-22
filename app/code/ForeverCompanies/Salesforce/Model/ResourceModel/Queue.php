<?php

namespace ForeverCompanies\Salesforce\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Queue
 *
 * @package  ForeverCompanies\Salesforce\Model\ResourceModel
 */
class Queue extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('fc_salesforce_queue', 'queue_id');
    }
}
