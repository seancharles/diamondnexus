<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Observer\Order;


use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Observer\SyncObserver;
use ForeverCompanies\Salesforce\Model\Sync\Order;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;


/**
 * Class Create
 */
class Create extends SyncObserver
{

    /**
     * @var \ForeverCompanies\Salesforce\Model\Sync\Order
     */
    protected $order;

    /**
     * Create constructor.
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $config
     * @param Order $order
     */
    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config,
        Order $order
    ) {
        $this->order  = $order;
        parent::__construct($queueFactory, $config);
    }

    /**
     * Order execute handler
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
                /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $salesforceId = $order->getData('customer_sf_acctid');
            $orderSalesforceId = $order->getData('sf_orderid');
            $orderSalesforceGuestId = $order->getData('sf_order_itemid');
            $increment_id = $order->getIncrementId();
            if ($salesforceId){

                 if(!$orderSalesforceId && $orderSalesforceId == null){
                       $this->order->sync($salesforceId,$increment_id,false,"");
                 }else if ($orderSalesforceId && !$orderSalesforceGuestId){
                       $this->order->sync($salesforceId,$increment_id,false,$orderSalesforceId);
                 }

            }else if(!$orderSalesforceGuestId && $orderSalesforceGuestId == null && !$orderSalesforceId){
                $this->order->sync($salesforceId,$increment_id,true,"");
            }

    }
}
