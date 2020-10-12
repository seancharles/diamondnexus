<?php

namespace DiamondNexus\Multipay\Model\ResourceModel\Transaction;

use DiamondNexus\Multipay\Model\Transaction;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package DiamondNexus\Multipay\Model\ResourceModel\Transaction
 */
class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(Transaction::class, ResourceModel::class);
    }
}
