<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\Connection;

use Magento\Backend\Block\Template;

class Status extends Template
{
    /**
     * Set Template
     *
     * @var string
     */
    protected $_template = 'system/config/connection/status.phtml';

    public function isConnected()
    {
        return $this->_scopeConfig->isSetFlag('salesforcecrm/config/is_connected');
    }
}
