<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use ForeverCompanies\Salesforce\Api\OrderSalesforceInterface;
use ForeverCompanies\Salesforce\Model\Sync\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session;

/**
 * Class OrderSalesforce
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 * @method Map setStatus(int $status)
 */
class OrderSalesforce  implements  OrderSalesforceInterface
{

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var Sync\Order
     */
    protected $order;

    public function __construct(
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        Order $order){

        $this->checkoutSession =  $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->order = $order;
    }

    public function createOrder(){

           $orderId = $this->checkoutSession->getData('last_order_id');
           $lastOrder = $this->orderRepository->get($orderId);
            /** @var \Magento\Sales\Model\Order $order */
                if (! $lastOrder->getData(Order::SALESFORCE_ORDER_ATTRIBUTE_CODE)) {
                    $increment_id = $lastOrder->getIncrementId();
                    $this->order->sync($increment_id);
                }
    }


    public function  updateOrder($orderId){
        // to do
    }

}
