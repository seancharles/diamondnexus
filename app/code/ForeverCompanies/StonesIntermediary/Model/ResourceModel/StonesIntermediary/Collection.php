<?php

namespace ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesIntermediary;

use ForeverCompanies\StonesIntermediary\Model\StonesIntermediary;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesIntermediary as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'forevercompanies_stones_intermediary_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'stones_intermediary_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StonesIntermediary::class, ResourceModel::class);
    }
}
