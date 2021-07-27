<?php

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Queue
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 */
class Queue extends AbstractModel
{
    const TYPE_ACCOUNT = 'Account';
    const TYPE_ORDER = 'Order';

    protected function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\ResourceModel\Queue');
    }
}
