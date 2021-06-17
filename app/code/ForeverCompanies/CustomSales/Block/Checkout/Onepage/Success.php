<?php
namespace ForeverCompanies\CustomSales\Block\Checkout\Onepage;

use ShipperHQ\Shipper\Model\ResourceModel\Order\Detail;

class Success extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        Detail $shipperDetailResourceModel,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->shipperDetailResourceModel = $shipperDetailResourceModel;
        
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
    }
    /**
     * @return int
     */
    public function getDeliveryDate()
    {
        $orderId = $this->_checkoutSession->getLastRealOrder()->getId();
        
        $connection = $this->shipperDetailResourceModel->getConnection();
        $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
            ->where('order_id = ?', $orderId)
            ->order('id desc')
            ->limit(1);
        
        $data = $connection->fetchRow($select);
        
        if(isset($data['delivery_date']) === true) {
            return $data['delivery_date'];
        }
        
        return null;
    }
}