<?php
namespace ForeverCompanies\StonesIntermediary\Model\ResourceModel;

use ForeverCompanies\StonesIntermediary\Model\Spi\StonesIntermediaryResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StonesIntermediary extends AbstractDb implements StonesIntermediaryResourceInterface
{
    /**
     * @var string
     */
    protected $mainTable = 'stones_intermediary';

    protected function _construct()
    {
        $this->_init($this->mainTable, 'id');
    }
}
