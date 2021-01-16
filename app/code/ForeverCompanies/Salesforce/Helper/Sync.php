<?php

    namespace ForeverCompanies\Salesforce\Helper;

    use Magento\Framework\App\Helper\AbstractHelper;

    use Magento\Framework\Stdlib\DateTime\DateTime;
    use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

    use ForeverCompanies\Salesforce\Model\Sync\Account;
    use ForeverCompanies\Salesforce\Model\Sync\Order;
    use ForeverCompanies\Salesforce\Model\Sync\Lead;

    class Sync extends AbstractHelper
    {
        protected $orderFactory;
        protected $orderRepositoryInterface;
        protected $customerFactory;
        protected $customerResourceFactory;
        protected $leadsCollectionFactory;
        protected $leadsFactory;
        
        protected $date;
        protected $timezone;
        
        protected $fcSyncOrder;
        protected $fcSyncAccount;
        protected $fcSyncLead;
        
        const PAGE_SIZE = 1000;
        
        const SF_CUSTOMER_ID_FIELD = 'sf_acctid';
        const SF_ORDER_ID_FIELD = 'sf_orderid';
        const SF_LAST_SYNC_FIELD = 'lastsync_at';
        
        public function __construct(
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
            \Magento\Customer\Model\CustomerFactory $customerFactory,
            \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
            \ForeverCompanies\Forms\Model\ResourceModel\Submission\CollectionFactory $leadsCollectionFactory,
            \ForeverCompanies\Forms\Model\ResourceModel\SubmissionFactory $leadsFactory,
            
            DateTime $date,
            TimezoneInterface $timezone,
            
            Order $fcSyncOrder,
            Account $fcSyncAccount,
            Lead $fcSyncLead
        )
        {
            $this->orderFactory = $orderFactory;
            $this->orderRepositoryInterface = $orderRepositoryInterface;
            $this->customerFactory = $customerFactory;
            $this->customerResourceFactory = $customerResourceFactory;
            $this->leadsCollectionFactory = $leadsCollectionFactory;
            $this->leadsFactory = $leadsFactory;
            
            $this->date = $date;
            $this->timezone = $timezone;
            
            $this->fcSyncAccount = $fcSyncAccount;
            $this->fcSyncOrder = $fcSyncOrder;
            $this->fcSyncLead = $fcSyncLead;
        }
        
        /**
         * Execute the sync
         *
         * @param InputInterface $input
         * @param OutputInterface $output
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
        
        /**
         * Execute the sync
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return null|int
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
        
        protected function getOrderCollection()
        {
            $collection = $this->orderFactory->create()
                ->addFieldToFilter(
                    array("main_table.updated_at","main_table.created_at"),
                    array(
                        array('gt' => $this->getFilterDate()),
                        array('gt' => $this->getFilterDate())
                    )
                );
            
            $collection->getSelect()->joinLeft(
                array('sosh' => 'sales_order_status_history'),
                "main_table.entity_id = sosh.parent_id AND sosh.created_at > '{$this->getFilterDate()}'"
            );
            
            $collection->getSelect()->joinLeft(
                array('sst' => 'sales_shipment_track'),
                "main_table.entity_id = sst.order_id AND sst.updated_at > '{$this->getFilterDate()}'"
            );
            
            $collection->getSelect()->joinLeft(
                array('soa' => 'sales_order_address'),
                "main_table.entity_id = soa.parent_id AND soa.address_updated_at > '{$this->getFilterDate()}'"
            );
            
            $collection->getSelect()
                ->reset(\Zend_Db_Select::COLUMNS)
                ->columns([
                    'entity_id',
                    'increment_id',
                    'customer_id',
                    'lastsync_at',
                    'sf_orderid',
                    'created_at',
                    'updated_at',
                    'MAX(sosh.created_at) AS sosh_created_at',
                    'MAX(soa.address_updated_at) AS soa_updated_at',
                    'MAX(sst.updated_at) AS sst_updated_at'
                ]);
            
            $collection->getSelect()->group('main_table.entity_id')->order('main_table.updated_at DESC');
            $collection->setPageSize(self::PAGE_SIZE);
            
            return $collection;
        }
        
        protected function processOrders($orders)
        {
            // handle looping through large collections
            for($i=1; $i<= $this->getPageCount($orders); $i++)
            {
                $orders->setCurPage($i);
                
                foreach($orders as $order)
                {
                    $lastSync = strtotime($order->getData(self::SF_LAST_SYNC_FIELD));
                    $updatedAt = strtotime($order->getData('updated_at'));
                    
                    // joined columns for sales order status history
                    $soshUpdatedAt = strtotime($order->getData('sosh_created_at'));
                    // sales order address
                    $soaUpdatedAt = strtotime($order->getData('soa_updated_at'));
                    // sales shipmnet tracking
                    $sstUpdatedAt = strtotime($order->getData('sst_updated_at'));
                    
                    $sfAccountId = null;
                    
                    if(
                        $lastSync < $updatedAt ||
                        $lastSync < $soshUpdatedAt ||
                        $lastSync < $soaUpdatedAt ||
                        $lastSync < $sstUpdatedAt
                    ) {
                        // load order instance for updating
                        $orderInstance = $this->orderRepositoryInterface->get($order->getId());
                        
                        $sfOrderId = $orderInstance->getData(self::SF_ORDER_ID_FIELD);
                        
                        if(!$lastSync || !$sfOrderId) {
                            
                            // pull the order id from sales force (if it exists)
                            $sfOrderId = $this->fcSyncOrder->searchRecord('Order', 'Web_Order_Id__c', $order->getId());
                            
                            if($order->getCustomerId()) {
                                // pull the customer id from sales force (if it exists)
                                $sfAccountId = $this->fcSyncAccount->searchRecord('Account', 'Web_Account_Id__c', $order->getCustomerId());
                                
                                // when customer isn't in SF create an account
                                if(!$sfAccountId) {
                                    $customerInstance = $this->customerFactory->create()->load($orderInstance->getCustomerId());
                                    
                                    // no salesforce account id on record create the account before adding the order
                                    if(!$sfAccountId) {
                                        $this->logOutput("Sync Account: " . $customerInstance->getEmail());
                                        
                                        $sfAccountId = $this->fcSyncAccount->sync($customerInstance->getId());
                                        
                                        $this->setCustomerAttributes($orderInstance->getCustomerId(), $sfAccountId);
                                    }
                                }
                                
                            } else {
                                // lookup by email for guest orders
                                $sfAccountId = $this->fcSyncAccount->searchRecord('Account', 'PersonEmail', $order->getCustomerEmail());
                            }
                            
                            // sync new order for customer with account and guests
                            $newSfOrderId = $this->fcSyncOrder->sync($order->getIncrementId(), $sfOrderId, $sfAccountId);
                            
                            // new accounts return SF account id
                            if($newSfOrderId) {
                                $orderInstance->setData(self::SF_ORDER_ID_FIELD, $newSfOrderId);
                            } elseif($sfOrderId) {
                                $orderInstance->setData(self::SF_ORDER_ID_FIELD, $sfOrderId);
                            } else {
                                $this->logOutput("Error unable to find or create order.");
                            }
                            
                        } else {
                            // process order updates for existing orders that have all SF ids
                            $this->fcSyncOrder->sync($order->getIncrementId(), $sfOrderId, $sfAccountId);
                        }
                        
                        // always update the last sync time
                        $orderInstance->setData('lastsync_at', $this->date->gmtDate());
                        
                        $this->orderRepositoryInterface->save($orderInstance);
                        
                        $this->logOutput("Sync Order Items");
                        
                        // every time an order is processed the order items are replaced currently
                        $this->fcSyncOrder->syncLineItems($order->getId(), $this->date->gmtDate());
                    }
                }
            }
        }

        protected function getCustomersCollection()
        {
            $collection = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addFieldToFilter(
                    array(
                        array('attribute'=> 'updated_at', 'gt' => $this->getFilterDate()),
                        array('attribute' => 'created_at', 'gt' => $this->getFilterDate())
                    )
                )
                ->setPageSize(self::PAGE_SIZE)
                ->load();
            
            return $collection;
        }
        
        protected function processCustomers($customers)
        {
            // handle looping through large collections
            for($i=1; $i<= $this->getPageCount($customers); $i++)
            {
                $customers->setCurPage($i);
                
                foreach($customers as $customer)
                {
                    $lastSync = strtotime($customer->getData(self::SF_LAST_SYNC_FIELD));
                    $updatedAt = strtotime($customer->getData('updated_at'));
                    $customerId = $customer->getId();
                    
                    if($updatedAt > $lastSync) {
                        
                        $this->logOutput("Sync " . $customer->getEmail());
                        
                        // pull the account if it exists in SF
                        $sfAccountId = $this->fcSyncAccount->searchRecord('Account', 'Web_Account_Id__c', $customerId);

                        $sfNewAccountId = $this->fcSyncAccount->sync($customerId, $sfAccountId);
                        
                        if($sfNewAccountId) {
                            $this->setCustomerAttributes($customerId, $sfNewAccountId);
                        } elseif($sfAccountId) {
                            $this->setCustomerAttributes($customerId, $sfAccountId);
                        } else {
                            $this->logOutput("Error: unable to located account in Salesforce via API.");
                        }
                        
                        $this->logOutput("Sync complete");
                    }
                }
            }
        }
        
        protected function getLeadsCollection()
        {
            $collection = $this->leadsCollectionFactory->create()
                ->addFieldToFilter(
                    array("main_table.created_at"),
                    array(
                        array('gt' => $this->getFilterDate())
                    )
                )
                // form ids to sync to salesforce, this should be maintained
                ->addFieldToFilter("form_id", array('in' => array(1,2,3)))
                ->setPageSize(self::PAGE_SIZE)
                ->load();
            
            return $collection;
        }
        
        protected function processLeads($leads)
        {
            // handle looping through large collections
            for($i=1; $i<= $this->getPageCount($leads); $i++)
            {
                $leads->setCurPage($i);
                
                foreach($leads as $lead)
                {
                    if($lead->getData(self::SF_LAST_SYNC_FIELD) == null) {
                        
                        $this->logOutput("Sync " . $lead->getEmail());
                        
                        $postData = json_decode($lead->getData('form_post_json'));

                        $leadData = [];
                        $leadData['RecordTypeId'] = '0120v000000X2vcAAC';
                        $leadData['email'] = $lead->getEmail();
                        
                        if(isset($postData['firstname']) == true) {
                            $leadData['firstname'] = $lead->getFirstname();
                        }
                        
                        if(isset($postData['lastname']) == true) {
                            $leadData['lastname'] = $lead->getLastname();
                        }
                        
                        $leadId = $this->fcSyncLead->sync([
                            'lead' => $leadData
                        ]);
                        
                        if($leadId) {
                            // always update the last sync time
                            $lead->setData('lastsync_at', $this->date->gmtDate());
                            
                            $this->leadsFactory->create()->save($lead);
                        }
                        
                        $this->logOutput("Snyc complete");
                    }
                }
            }
        }
        
        protected function setCustomerAttributes($customerId, $sfAcctId = false) {
            
            $customer = $this->customerFactory->create();

            $customerData = $customer->getDataModel();
            $customerData->setId($customerId);

            if($sfAcctId) {
                $customerData->setCustomAttribute(self::SF_CUSTOMER_ID_FIELD, $sfAcctId);
            }
            
            $customerData->setCustomAttribute(self::SF_LAST_SYNC_FIELD, $this->date->gmtDate());

            $customer->updateData($customerData);

            $customerResource = $this->customerResourceFactory->create();

            if ($sfAcctId) {
                $customerResource->saveAttribute($customer, self::SF_CUSTOMER_ID_FIELD);
            }
            
            $customerResource->saveAttribute($customer, self::SF_LAST_SYNC_FIELD);
        }
        
        protected function logOutput($message, $pushConsole = true)
        {
            if($pushConsole) {
                echo $message . "\n";
            }
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/salesforce.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($message);
        }
        
        protected function getPageCount($collection) {
            return ceil($collection->getSize() / self::PAGE_SIZE);
        }
        
        protected function getFilterDate()
        {
            return date("Y-m-d", strtotime("-2 days")) . ' 00:00:00';
        }
    }