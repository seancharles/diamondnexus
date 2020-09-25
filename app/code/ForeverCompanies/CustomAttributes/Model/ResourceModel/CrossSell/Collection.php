<?php

namespace ForeverCompanies\CustomAttributes\Model\ResourceModel\CrossSell;

use ForeverCompanies\CustomAttributes\Model\CrossSell;
use ForeverCompanies\CustomAttributes\Model\ResourceModel\CrossSell as ResourceModel;
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
    protected $_eventPrefix = 'forevercompanies_customattributes_crosssell_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'crosssell_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(CrossSell::class, ResourceModel::class);
    }
}
