<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\Create;

use Magento\Framework\Exception\LocalizedException;

class SalesPerson extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * @return string
     */
    public function getSalesPerson()
    {
        try {
            return $this->getQuote()->getData('sales_person_id') ?? 'nobody';
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }
    }
}
