<?php

namespace DiamondNexus\Multipay\Block\Order;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Sales\Model\Order;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Sales\Block\Order\History
{
    /**
     * @var string
     */
    protected $_template = 'DiamondNexus_Multipay::order/history.phtml';

    /**
     * @param Order $order
     * @return bool
     */
    public function canPayNow(Order $order)
    {
        $method = $order->getPayment()->getMethod();
        return $method == Constant::MULTIPAY_METHOD && $order->getStatus() !== Order::STATE_PROCESSING;
    }

    public function getPayNowUrl($id)
    {
        return $this->getUrl('diamondnexus/order/paynow', ['order_id' => $id]);
    }

}
