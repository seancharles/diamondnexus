<?php

namespace ForeverCompanies\Salesforce\Observer\Customer;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class CustomerAddressSaveAfter implements ObserverInterface
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
        $customerAddress = $observer->getCustomerAddress();

        $queueModel = $this->queueFactory->create();
        $queueModel->addData([
            "entity_id" => (int) $customerAddress->getCustomerId(),
            "entity_type" => "customer_address",
            "created_at" => date("Y-m-d h:i:s")
        ]);
        $queueModel->save();
    }
}
