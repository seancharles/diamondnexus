<?php

namespace ForeverCompanies\Salesforce\Observer\Customer;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class CustomerSaveAfter implements ObserverInterface
{
    protected $queueFactory;

    protected $fields = [];

    public function __construct(
        QueueFactory  $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
    }

    public function execute(Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getData('customer_data_object');
        $oldCustomer = $observer->getData('orig_customer_data_object');

        $queueModel = $this->queueFactory->create();
        $queueModel->addData([
            "entity_id" => (int) $customer->getId(),
            "entity_type" => "customer",
            "created_at" => date("Y-m-d h:i:s")
        ]);
        $queueModel->save();
    }
}
