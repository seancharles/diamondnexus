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
     * ID Field name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'forevercompanies_salesforce_report_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'report_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\Report',
        'ForeverCompanies\Salesforce\Model\ResourceModel\Report');
    }

}
