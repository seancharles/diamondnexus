<?php

namespace ForeverCompanies\CustomApi\Block\Adminhtml\Order;

/**
 * Class AsdAndDeliveryModalBox
 * @package ForeverCompanies\CustomApi\Block\Adminhtml\Order
 */
class AsdAndDeliveryModalBox extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
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
