<?php
namespace ForeverCompanies\CustomApi\Model\ResourceModel;

use ForeverCompanies\CustomApi\Model\Spi\ExtSalesOrderUpdateResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ExtSalesOrderUpdate extends AbstractDb implements ExtSalesOrderUpdateResourceInterface
{
    /**
     * @var string
     */
    protected $mainTable = 'ext_sales_order_updates';

    protected function _construct()
    {
        $this->_init($this->mainTable, 'entity_id');
    }
}
