<?php

namespace ForeverCompanies\CustomApi\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;

/**
 * Class AsdAndDeliveryModalBox
 * @package ForeverCompanies\CustomApi\Block\Adminhtml\Order
 */
class AsdAndDeliveryModalBox extends \Magento\Backend\Block\Template
{

    public function getFormUrl()
    {
        $orderId = false;
        if ($this->hasData('order')) {
            $orderId = $this->getData('order')->getId();
        }
        return $this->getUrl('forevercompanies/order/order', [
            'order_id' => $orderId
        ]);
    }
}
