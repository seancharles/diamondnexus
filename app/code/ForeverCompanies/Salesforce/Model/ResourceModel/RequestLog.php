<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class RequestLog extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('forevercompanies_salesforce_request', 'id');
    }
}
