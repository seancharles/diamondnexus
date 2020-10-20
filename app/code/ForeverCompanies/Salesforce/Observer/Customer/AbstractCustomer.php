<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Observer\Customer;

use ForeverCompanies\Salesforce\Model\Queue;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\Sync\Account;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;


abstract class AbstractCustomer implements ObserverInterface
{
    const XML_SETTING_PATH = 'salesforcecrm/sync/';

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Account
     */
    protected $_account;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * AbstractCustomer constructor.
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $config
     * @param Account $account
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */

    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config,
        Account $account,
        CustomerRepositoryInterface $customerRepositoryInterface

    ) {

        $this->_account     = $account;
        $this->queueFactory = $queueFactory;
        $this->scopeConfig  = $config;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function getEnableConfig($type){

        $path = self::XML_SETTING_PATH . $type;
        return $this->scopeConfig->getValue($path);
    }

    public function getSyncModeConfig($type){

        $path = self::XML_SETTING_PATH . $type . '_mode';
        return $this->scopeConfig->getValue($path);
    }

    /**
     * @param Customer $customer
     */
    public function syncAccount(Customer $customer)
    {
       if ($this->getEnableConfig('account')) {
           if ($this->getSyncModeConfig('account') == 1) {
               /** add to queue mode */
               $this->addToQueue(Queue::TYPE_ACCOUNT, $customer->getId());
           }
       }else {
                /** auto sync mode */
                $id = $customer->getId();
                $customer = $this->customerRepositoryInterface->getById($id);
                $customerAttributeData = $customer->__toArray();
                $empty = empty($customerAttributeData['custom_attributes']['sf_acctid']['value']);
                if ($empty){
                    $this->_account->sync($id, "");
                }else {
                    $salesforceId = $customerAttributeData['custom_attributes']['sf_acctid']['value'];
                    $this->_account->sync($id,$salesforceId);
                }


       }

    }

    public function addToQueue($type, $entityId){

        /** add to queue mode */
        $queue = $this->queueFactory->create()
            ->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->getFirstItem();
        if ($queue->getId()){
            /** Creditmemo existed in queue */
            $queue =  $this->queueFactory->create()->load($queue->getId());
            $queue->setEnqueueTime(time());
            $queue->save();
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
    }


}
