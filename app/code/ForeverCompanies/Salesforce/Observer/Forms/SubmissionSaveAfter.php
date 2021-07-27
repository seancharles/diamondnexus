<?php

namespace ForeverCompanies\Salesforce\Observer\Forms;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ForeverCompanies\Salesforce\Model\QueueFactory;

class SubmissionSaveAfter implements ObserverInterface
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
        $submission = $observer->getEvent()->getDataObject();

        $formList = [1, 2, 3, 4, 7];

        // form ids to sync to salesforce, this should be maintained
        if(in_array($submission->getFormId(), $formList) === true) {

            $queueModel = $this->queueFactory->create();
            $queueModel->addData([
                "entity_id" => (int) $submission->getSubmissionId(),
                "entity_type" => "lead",
                "created_at" => date("Y-m-d h:i:s")
            ]);
            $queueModel->save();
        }
    }
}
