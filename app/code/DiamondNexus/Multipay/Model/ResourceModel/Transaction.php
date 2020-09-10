<?php

namespace DiamondNexus\Multipay\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;


class Transaction extends AbstractDb
{
    protected $mainTable = 'diamondnexus_multipay_transaction';
    protected $primaryKey = 'id';

    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init($this->mainTable, $this->primaryKey);
    }
}