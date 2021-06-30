<?php

namespace ForeverCompanies\TealiumCron\Model;

use Magento\Framework\Model\AbstractModel;

class Event extends AbstractModel
{
    protected function _construct() {
        $this->_init('ForeverCompanies\TealiumCron\Model\Resource\Event');
    }
}