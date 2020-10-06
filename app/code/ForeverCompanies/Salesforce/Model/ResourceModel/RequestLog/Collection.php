<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\ResourceModel\Report;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package ForeverCompanies\Salesforce\Model\ResourceModel\Report
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\RequestLog',
        'ForeverCompanies\Salesforce\Model\ResourceModel\RequestLog');
    }
}
