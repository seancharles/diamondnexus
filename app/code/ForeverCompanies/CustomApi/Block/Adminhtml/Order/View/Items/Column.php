<?php

namespace ForeverCompanies\CustomApi\Block\Adminhtml\Order\View\Items;

use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

class Column extends DefaultColumn
{
    /**
     * @return string
     */
    public function getFlagLooseStone()
    {
        $flag = (bool)$this->getItem()->getData('flag_loose_stone');
        return $flag ? 'Yes' : 'No';
    }
}
