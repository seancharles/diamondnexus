<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Observer;

use ForeverCompanies\Salesforce\Model\QueueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;

abstract class SyncObserver implements ObserverInterface
{
    /**
     * @var string
     */
    protected $pathEnable = '';

    /**
     * @var string
     */
    protected $pathSyncOption = '';

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config
    ) {
        $this->queueFactory = $queueFactory;
        $this->scopeConfig = $config;
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path);
    }



    public function addToQueue($type, $entityId)
    {
        /** add to queue mode */
        $queue = $this->queueFactory->create()
            ->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->getFirstItem();

        if ($queue->getId()) {

            /** Creditmemo existed in queue */
            $queue = $this->queueFactory->create()->load($queue->getId());
            $queue->setEnqueueTime(time());
            $queue->save();
            return;
        }
        $queue = $this->queueFactory->create();
        $data = [
            'type' => $type,
            'entity_id' => $entityId,
            'enqueue_time' => time(),
            'priority' => 1,
        ];
        $queue->setData($data);
        $queue->save();
        return;
    }
}
