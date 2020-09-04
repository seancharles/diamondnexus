<?php

namespace ForeverCompanies\CustomSales\Block\Adminhtml\Order\View;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;

class LoggedUser extends AbstractOrder
{
    /**
     * @return string
     */
    public function getLoggedUser()
    {
        try {
            return $this->getOrder()->getData('loggeduser') ?? 'not edited';
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }
    }
}
