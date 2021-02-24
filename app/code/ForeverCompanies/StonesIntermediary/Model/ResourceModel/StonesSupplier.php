<?php
namespace ForeverCompanies\StonesIntermediary\Model\ResourceModel;

use ForeverCompanies\StonesIntermediary\Model\Spi\StonesSupplierResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StonesSupplier extends AbstractDb implements StonesSupplierResourceInterface
{
    /**
     * @var string
     */
    protected $mainTable = 'stones_supplier';

    protected function _construct()
    {
        $this->_init($this->mainTable, 'id');
    }
}
