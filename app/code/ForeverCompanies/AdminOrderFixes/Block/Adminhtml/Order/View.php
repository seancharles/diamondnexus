<?php

namespace ForeverCompanies\AdminOrderFixes\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrigView;

class View extends OrigView
{
    protected $allowedStatusArray;
    
    protected function _construct()
    {
        $this->allowedStatusArray = array(
            "pending_payment",
            "pending",
            "Payment Pending",
            "processing",
            "quote"
        );
        
        $this->_objectId = 'order_id';
        $this->_controller = 'adminhtml_order';
        $this->_mode = 'view';
        
        parent::_construct();
        
        $this->removeButton('order_cancel');
        $this->setId('sales_order_view');
        $order = $this->getOrder();
        
        if (!$order) {
            return;
        }
        
        if (!$order->isCanceled() && in_array($order->getStatus(), $this->allowedStatusArray)) {
            $this->addButton(
                'order_cancel',
                [
                    'label' => __('Cancel'),
                    'class' => 'cancel pz',
                    'id' => 'order-view-cancel-button',
                    'data_attribute' => [
                        'url' => $this->getCancelUrl()
                    ]
                ]
            );
        }
    }
}