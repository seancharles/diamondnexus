<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Queue
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 */
class Queue extends AbstractModel
{
    const TYPE_ACCOUNT = 'Account';
    const TYPE_ORDER = 'Order';

    protected function _construct()
    {
        $this->_init(ResourceModel\Queue::class);
    }

    public function queueExisted($type, $entityId)
    {
        $existedQueue = $this->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->getFirstItem();
        if ($existedQueue->getId()) {
            /** existed in queue */
            $queue = $this->load($existedQueue->getId());
            $queue->setEnqueueTime(time());
            $queue->save();
            return true;
        }
        return false;
    }

    public function enqueue($type, $entityId)
    {
        $data = [
            'type' => $type,
            'entity_id' => $entityId,
            'enqueue_time' => time(),
            'priority' => 1,
        ];
        $this->setData($data);
        $this->save();
    }

    public function getQueueByType($type)
    {
        $queue = $this->getCollection()
            ->addFieldToFilter('type', $type);
        return $queue;
    }
}
