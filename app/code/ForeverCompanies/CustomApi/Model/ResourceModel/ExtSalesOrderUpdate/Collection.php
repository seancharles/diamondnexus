<?php

namespace ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate;

use ForeverCompanies\CustomApi\Model\ExtSalesOrderUpdate;
use ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'forevercompanies_customapi_extsalesorderupdates_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'extsalesorderupdates_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExtSalesOrderUpdate::class, ResourceModel::class);
    }
}
