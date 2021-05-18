<?php

namespace ForeverCompanies\CustomApi\Block\Adminhtml\Order;

//use Magento\Framework\View\Element\Template;

use Magento\Backend\Block\Template;
//use Magento\Backend\App\Action;
//use Magento\Backend\App\Action\Context;

/**
 * Class AsdAndDeliveryModalBox
 * @package ForeverCompanies\CustomApi\Block\Adminhtml\Order
 */
class AsdAndDeliveryModalBox extends Template
{
    public function __construct(
        Template\Context $context,
        \ShipperHQ\Shipper\Model\ResourceModel\Order\Detail $shipperDetailResourceModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->shipperDetailResourceModel = $shipperDetailResourceModel;
    }
    
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
    
    public function getShipperDetailData($field = null)
    {
        $allowedFields = [
            'dispatch_date',
            'delivery_date'
        ];
        
        if(in_array($field, $allowedFields) === true) {
            
            if ($this->hasData('order')) {
                $orderId = $this->getData('order')->getId();
                
                $connection = $this->shipperDetailResourceModel->getConnection();
                $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
                    ->where('order_id = ?', $orderId)
                    ->order('id desc')
                    ->limit(1);
                $data = $connection->fetchRow($select);
                
                if(isset($data[$field]) === true) {
                    return date('Y-m-d', strtotime($data[$field]));
                }
            }
        }
        
        return null;
    }
}
