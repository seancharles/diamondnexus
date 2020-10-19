<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\ResourceModel\Map;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
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
    protected $_eventPrefix = 'forevercompanies_salesforce_map_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'map_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce\Model\Map',
            'ForeverCompanies\Salesforce\Model\ResourceModel\Map');
    }
}
