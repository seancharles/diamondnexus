<?php
namespace ForeverCompanies\CustomAttributes\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class CrossSell extends AbstractDb
{
    /**
     * @var string
     */
    protected $mainTable = 'catalog_product_cross_sell';

    protected function _construct()
    {
        $this->_init($this->mainTable, 'id');
    }

    /**
     * @return Select
     * @throws LocalizedException
     */
    public function getCrossSellSelect()
    {
        $connection = $this->getConnection();
        return $connection->select()->reset()->from(
            ['main_table' => $this->getMainTable()]
        )->joinInner(
            ['entity_varchar' => 'catalog_product_entity_varchar'],
            'main_table.product_id = entity_varchar.row_id'
        )->joinInner(
            ['entity' => 'catalog_product_entity'],
            'main_table.product_id = entity.entity_id'
        );
    }
}
