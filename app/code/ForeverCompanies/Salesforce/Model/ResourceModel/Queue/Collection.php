<?php

namespace ForeverCompanies\Salesforce\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'ForeverCompanies\Salesforce\Model\Queue',
            'ForeverCompanies\Salesforce\Model\ResourceModel\Queue'
        );
    }
}
