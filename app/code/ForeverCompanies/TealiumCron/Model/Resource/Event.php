<?php

namespace ForeverCompanies\TealiumCron\Model\Resource;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Event extends AbstractDb
{
    protected function _construct() {
        $this->_init('forevercompanies_tealium_event', 'id');
    }
}