<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Observer\Order;

use ForeverCompanies\Salesforce\Model\Queue;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Observer\SyncObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\Salesforce\Model\Sync\Order;
use Magento\Framework\Event\Observer;

/**
 * Class Create
 */
class Create extends SyncObserver
{
    /**
     * @var string
     */
    protected $pathEnable = 'salesforcecrm/sync/order';
    protected $pathSyncOption = 'salesforcecrm/sync/order_mode';

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
    public function __construct(QueueFactory $queueFactory,
                                ScopeConfigInterface $config,
                                Order $order)
    {
        $this->order = $order;
        parent::__construct($queueFactory, $config);
    }

    /**
     * Admin/Cutomer edit information address
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->getConfigValue($this->pathEnable)) {

            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            if ($this->getConfigValue($this->pathSyncOption) == 1){
                $this->addToQueue(Queue::TYPE_ORDER, $order->getIncrementId());
            } else {
                /** @var \Magento\Sales\Model\Order $order */
                $order = $observer->getEvent()->getOrder();
                if (!$order->getData(Order::SALESFORCE_ORDER_ATTRIBUTE_CODE)){
                    $increment_id = $order->getIncrementId();
                    $this->order->sync($increment_id);
                }
            }
        }
    }
}
