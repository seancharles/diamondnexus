<?php

namespace ForeverCompanies\TealiumCron\Model\Resource\Event;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

// use ForeverCompanies\TealiumCron\Model\Event as EventModel;
// use ForeverCompanies\TealiumCron\Model\Event\Eevent as EventEventModel;

class Collection extends AbstractCollection
{
    protected function _construct() {
        $this->_init(
            'ForeverCompanies\TealiumCron\Model\Event',
            'ForeverCompanies\TealiumCron\Model\Resource\Event'
        );
    }
    
}