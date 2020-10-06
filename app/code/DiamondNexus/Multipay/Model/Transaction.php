<?php

namespace DiamondNexus\Multipay\Model;

use Magento\Cron\Exception;
use Magento\Framework\Model\AbstractModel;


class Transaction extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Transaction::class);
    }
}