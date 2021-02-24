<?php

namespace ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesSupplier;

use ForeverCompanies\StonesIntermediary\Model\StonesSupplier;
use ForeverCompanies\StonesIntermediary\Model\ResourceModel\StonesSupplier as ResourceModel;
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
    protected $_eventPrefix = 'forevercompanies_stones_supplier_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'stones_supplier_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(StonesSupplier::class, ResourceModel::class);
    }
}
