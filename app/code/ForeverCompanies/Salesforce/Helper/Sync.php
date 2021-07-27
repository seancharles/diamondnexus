<?php

namespace ForeverCompanies\Salesforce\Helper;

use Exception;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;
use ForeverCompanies\Forms\Model\SubmissionFactory;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Customer\Model\CustomerFactory;
use ForeverCompanies\Salesforce\Helper\Mapping;
use ForeverCompanies\Salesforce\Model\Sync\Account;
use ForeverCompanies\Salesforce\Model\Sync\Lead;
use ForeverCompanies\Salesforce\Model\Sync\Order;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Sync extends AbstractHelper
{
    const PAGE_SIZE = 10000;
    const SF_MAX_RETRY = 3;
    const SF_MAX_ERROR = 3;

    protected $queueFactory;
    protected $queueCollectionFactory;
    protected $submissionFactory;
    protected $orderFactory;
    protected $customerFactory;
    protected $mappingHelper;
    protected $scopeConfig;
    protected $date;
    protected $timezone;
    protected $fcSyncOrder;
    protected $fcSyncAccount;
    protected $fcSyncLead;
    protected $maxTime;

    protected $syncedLeads = [];
    protected $syncedCustomers = [];
    protected $syncedOrders = [];

    public function __construct(
        QueueFactory $queueFactory,
        QueueCollectionFactory $queueCollectionFactory,
        SubmissionFactory $submissionFactory,
        OrderInterfaceFactory $orderFactory,
        CustomerFactory $customerFactory,
        Mapping $mappingHelper,
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        TimezoneInterface $timezone,
        Order $fcSyncOrder,
        Account $fcSyncAccount,
        Lead $fcSyncLead
    )
    {
        $this->queueFactory = $queueFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
        $this->submissionFactory = $submissionFactory;
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->mappingHelper = $mappingHelper;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->fcSyncAccount = $fcSyncAccount;
        $this->fcSyncOrder = $fcSyncOrder;
        $this->fcSyncLead = $fcSyncLead;

        $this->maxTime = date("Y-m-d h:i:s");
    }

    /**
     * Execute the sync
     *
     * @return null|int
     */
    public function run()
    {
        $this->logOutput("Sync started");

        // get recently modified customers
        $customers = $this->getCustomersCollection();

        $this->logOutput($customers->getSize() . " customers found");

        $this->processCustomers($customers);

        // get recently modified orders
        $orders = $this->getOrderCollection();

        $this->logOutput($orders->getSize() . " orders found");

        $this->processOrders($orders);

        $this->logOutput("Sync completed");
    }

    protected function logOutput($message, $pushConsole = true)
    {
        if ($pushConsole) {
            echo $message . "\n";
        }

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/salesforce.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
    }

    protected function updateQueue($queueModel, $hasErrors = false, $errorMessage = null)
    {
        $retryCount = $queueModel->getData('try_count');
        $errorCount = $queueModel->getData('error_count');
        $errorText = $queueModel->getData('error_text');

        $queueModel->setData('try_count', $retryCount+1);

        if ($hasErrors === false) {
            $queueModel->setData('synced_at', date('Y-m-d h:i:s'));
        } else {
            $queueModel->setData('error_count', $errorCount+1);
            $queueModel->setData('error_text', $errorText . "\n" . $errorMessage);
        }
        $queueModel->save();
    }

    protected function getFilterDate()
    {
        return date("Y-m-d", strtotime("-1 days")) . ' 00:00:00';
    }

    protected function getPageCount($collection)
    {
        return ceil($collection->getSize() / self::PAGE_SIZE);
    }

    protected function getCustomersCollection()
    {
        return $this->queueCollectionFactory->create()
            ->addFieldToFilter('created_at', ['gt' => $this->getFilterDate()])
            ->addFieldToFilter('created_at', ['lt' => $this->maxTime])
            ->addFieldToFilter("entity_type", ['in' => ['customer','customer_address']])
            ->addFieldToFilter("synced_at", ['null' => true])
            ->addFieldToFilter("try_count", ['lteq' => self::SF_MAX_RETRY])
            ->addFieldToFilter("error_count", ['lteq' => self::SF_MAX_ERROR])
            ->setPageSize(self::PAGE_SIZE)
            ->load();
    }

    protected function processCustomers($customersQueue)
    {
        $pageCount = $this->getPageCount($customersQueue);

        $queueFactory = $this->queueFactory->create();
        $customerFactory = $this->customerFactory->create();

        for ($i = 1; $i <= $pageCount; $i++) {
            $customersQueue->setCurPage($i);

            foreach ($customersQueue as $customerEntry) {
                // load the queue entry to update the count or date
                $queueModel = $queueFactory->load($customerEntry->getQueueId());

                try {
                    $entityId = $customerEntry->getEntityId();

                    if (isset($this->syncedCustomers[$entityId]) === false) {
                        // add the customers buffer
                        $this->syncedCustomers[$entityId] = $entityId;

                        // load customer data
                        $customerModel = $customerFactory->load($entityId);

                        $customerId = $customerModel->getId();

                        $this->logOutput("Sync " . $customerModel->getEmail());

                        // pull the account if it exists in SF
                        $sfAccountId = $this->fcSyncAccount->searchRecord('Account', 'PersonEmail', $customerModel->getEmail());

                        if (isset($sfAccountId['status']) == true && $sfAccountId['status'] == 'error') {
                            $sfAccountId = null;
                        }

                        $sfNewAccountId = $this->fcSyncAccount->sync($customerId, $sfAccountId);

                        if (!$sfNewAccountId && !$sfAccountId) {
                            $this->logOutput("Error: unable to locate account in Salesforce via API.");

                            $this->updateQueue($queueModel, true, "Error: unable to locate account in Salesforce via API.");
                        } else {
                            $this->updateQueue($queueModel);
                        }

                        $this->logOutput("Sync complete");
                    } else {
                        $this->logOutput("Ignore Customer");
                        // TODO: this should be replaced by better queue management in the future
                        $this->updateQueue($queueModel);
                    }

                } catch(Exception $e) {
                    $this->updateQueue($queueModel, true, $e->getMessage());
                }
            }
        }
    }

    protected function getOrderCollection()
    {
        return $this->queueCollectionFactory->create()
            ->addFieldToFilter('created_at', ['gt' => $this->getFilterDate()])
            ->addFieldToFilter('created_at', ['lt' => $this->maxTime])
            ->addFieldToFilter("entity_type", ['in' => ['order','order_address','order_status_history']])
            ->addFieldToFilter("synced_at", ['null' => true])
            ->addFieldToFilter("try_count", ['lteq' => self::SF_MAX_RETRY])
            ->addFieldToFilter("error_count", ['lteq' => self::SF_MAX_ERROR])
            ->setPageSize(self::PAGE_SIZE)
            ->load();
    }

    protected function processOrders($ordersQueue)
    {
        $pageCount = $this->getPageCount($ordersQueue);

        $queueFactory = $this->queueFactory->create();
        $orderFactory = $this->orderFactory->create();

        for ($i = 1; $i <= $pageCount; $i++) {
            $ordersQueue->setCurPage($i);

            foreach ($ordersQueue as $orderEntry) {
                // load the queue entry to update the count or date
                $queueModel = $queueFactory->load($orderEntry->getQueueId());

                try {
                    $entityId = $orderEntry->getEntityId();

                    if (isset($this->syncedOrders[$entityId]) === false) {
                        // add the leads buffer
                        $this->syncedOrders[$entityId] = $entityId;

                        // load order data
                        $orderModel = $orderFactory->load($entityId);

                        // pull the order id from sales force (if it exists)
                        $sfOrderId = $this->fcSyncOrder->searchRecord('Order', 'Web_Order_Id__c', $orderModel->getId());

                        $sfAccountId = null;

                        if ($orderModel->getCustomerId()) {
                            // pull the customer id from sales force (if it exists)
                            $sfAccountId = $this->fcSyncAccount
                                ->searchRecord('Account', 'Web_Account_Id__c', $orderModel->getCustomerId());

                            // when customer isn't in SF create an account
                            if (!$sfAccountId) {
                                $customerModel = $this->customerFactory->create()->load($orderModel->getCustomerId());

                                $this->logOutput("Sync Account: " . $customerModel->getEmail());

                                $sfAccountId = $this->fcSyncAccount->sync($customerModel->getId());
                            }

                        } else {
                            // lookup by email for guest orders
                            $sfAccountId = $this->fcSyncAccount
                                ->searchRecord('Account', 'PersonEmail', $orderModel->getCustomerEmail());
                        }

                        // sync new order for customer with account and guests
                        $newSfOrderId = $this->fcSyncOrder->sync($orderModel->getIncrementId(), $sfOrderId, $sfAccountId);

                        if($newSfOrderId) {
                            $this->logOutput("Sync Order Items");

                            // every time an order is processed the order items are replaced currently
                            $this->fcSyncOrder->syncLineItems($orderModel->getId(), $newSfOrderId);

                            // update the number of tries
                            // TODO: add logic to make sure this was successful.
                            $this->updateQueue($queueModel);

                        } else {
                            $this->updateQueue($queueModel, true,"Invalid SF Order ID");
                        }
                    } else {
                        $this->logOutput("Ignore Order");
                        // TODO: this should be replaced by better queue management in the future
                        $this->updateQueue($queueModel);
                    }

                } catch(\Magento\Framework\Exception\LocalizedException $e) {
                    $this->updateQueue($queueModel, true, $e->getMessage());
                }
            }
        }
    }

    /**
     * Execute the sync
     *
     * @return bool|null
     */
    public function runLeads()
    {
        $this->logOutput("Sync started");

        // get recently modified customers
        $leads = $this->getLeadsCollection();

        $this->logOutput($leads->getSize() . " leads found");

        $this->processLeads($leads);

        $this->logOutput("Sync completed");
    }

    protected function getLeadsCollection()
    {
        $collection = $this->queueCollectionFactory->create()
            ->addFieldToFilter('created_at', ['gt' => $this->getFilterDate()])
            ->addFieldToFilter('created_at', ['lt' => $this->maxTime])
            ->addFieldToFilter("entity_type", 'lead')
            ->addFieldToFilter("synced_at", ['null' => true])
            ->addFieldToFilter("try_count", ['lteq' => self::SF_MAX_RETRY])
            ->addFieldToFilter("error_count", ['lteq' => self::SF_MAX_ERROR])
            ->setPageSize(self::PAGE_SIZE)
            ->load();

        return $collection;
    }

    protected function processLeads($leadsQueue)
    {
        $pageCount = $this->getPageCount($leadsQueue);

        $queueFactory = $this->queueFactory->create();
        $submissionFactory = $this->submissionFactory->create();

        for ($i = 1; $i <= $pageCount; $i++) {
            $leadsQueue->setCurPage($i);

            foreach ($leadsQueue as $leadEntry) {
                // load the queue entry to update the count or date
                $queueModel = $queueFactory->load($leadEntry->getQueueId());

                try {
                    $entityId = $leadEntry->getEntityId();

                    if (isset($this->syncedLeads[$entityId]) === false) {
                        // add the lead id to the current leads buffer
                        $this->syncedLeads[$entityId] = $entityId;

                        // load the form submission data
                        $leadModel = $submissionFactory->load($entityId);

                        $leadId = false;
                        $response = false;

                        $this->logOutput("Sync " . $leadModel->getEmail());

                        $postData = json_decode($leadModel->getData('form_post_json'));

                        $leadData = [
                            'RecordTypeId' => '0120v000000X2vcAAC',
                            'Brand__c' => $this->mappingHelper->getStoreCode($leadModel->getWebsiteId()),
                            'LeadSource' => 'Website',
                            'Email' => $leadModel->getEmail()
                        ];

                        if (isset($postData->firstname) == true) {
                            $leadData['FirstName'] = $postData->firstname;
                        } else {
                            $leadData['FirstName'] = $leadModel->getEmail();
                        }

                        if (isset($postData->firstname) == true) {
                            $leadData['LastName'] = $postData->lastname;
                        } else {
                            $leadData['FirstName'] = $leadModel->getEmail();
                        }

                        // get text representation of form identifier
                        $formCode = $this->mappingHelper->getFormCode($leadModel->getFormId());

                        switch ($formCode) {
                            case "fa-short":
                                // $leadData['Lead_Key__c'] = $lead->getLeadKey(); (removed since we change to unique emails)
                                $leadData['Phone'] = $this->getObjectKey($postData, 'telephone');
                                $leadData['SEM_campaign__c'] = $this->getObjectKey($postData, 'utms');
                                $leadData['lea13'] = 'Initial Inquiry';
                                $leadData['Lead_Assignment__c'] = 'fa_lead_queue';
                                break;

                            case "fa-long":
                                $leadData['DateNeeded__c'] = $this->getObjectKey($postData, 'selectNeedBy');
                                $leadData['PreferredMetalType__c'] = $this->getObjectKey($postData, 'selectMetalType');

                                $leadData['InspirationLink__c'] = $this->getObjectKey($postData, 'imageUploadOne');
                                $leadData['Inspiration_Link_2__c'] = $this->getObjectKey($postData, 'imageUploadTwo');
                                $leadData['Inspiration_Link_3__c'] = $this->getObjectKey($postData, 'imageUploadThree');

                                $leadData['Comments__c'] = $this->getObjectKey($postData, 'txtComments');
                                $leadData['JewelryType__c'] = $this->getObjectKey($postData, 'selectJewelryType');
                                $leadData['StoneCut__c'] = $this->getObjectKey($postData, 'selectShapePreference');
                                break;

                            case "tf-short":
                                $leadData['Phone'] = $this->getObjectKey($postData, 'telephone');
                                break;
                        }

                        // get lead id by email (previously was key)
                        $updateLeadId = $this->fcSyncAccount->searchRecord('Lead', 'Email', $leadModel->getEmail());

                        if ($updateLeadId) {
                            $this->logOutput("Updating lead");
                            $leadData['Id'] = $updateLeadId;
                            $response = $this->fcSyncLead->update(['lead' => $leadData]);
                        } else {
                            $this->logOutput("Creating lead");
                            $leadId = $this->fcSyncLead->create(['lead' => $leadData]);
                        }

                        if ($leadId || $response) {
                            $this->updateQueue($queueModel);
                            $this->logOutput("Saving lead");
                        } else {
                            $this->updateQueue($queueModel, true, "Lead was not able to sync");
                            $this->logOutput("Lead was not able to sync");
                        }
                    } else {
                        $this->logOutput("Ignore Order");
                        // TODO: this should be replaced by better queue management in the future
                        $this->updateQueue($queueModel);
                    }

                } catch(Exception $e) {
                    $this->updateQueue($queueModel, true, $e->getMessage());
                }
            }
        }
    }

    protected function getObjectKey($object, $key)
    {
        return (isset($date->{$object}) == true) ? $object->{$key} : '';
    }

    /*
     * @return bool
     */
    public function isLeadSyncEnabled($scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT): bool
    {
        return $this->scopeConfig->isSetFlag(
            'salesforcecrm/salesforceconfig/lead_sync_is_active',
            $scope
        );
    }

    /*
     * @return bool
     */
    public function isOrderSyncEnabled($scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            'salesforcecrm/salesforceconfig/order_sync_is_active',
            $scope
        );
    }
}