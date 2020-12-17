<?php
    namespace ForeverCompanies\Salesforce\Console\Command;
	
    use Symfony\Component\Console\Command\Command;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
	
	use ForeverCompanies\Salesforce\Model\Sync\Account;
	use ForeverCompanies\Salesforce\Model\Sync\Order;

    /**
     * Class SomeCommand
     */
    class SalesforceSync extends Command
    {
		/**
		 * @var string
		 */
		protected $name = 'forevercompanies:salesforce:sync';

		protected $orderFactory;
		protected $customerFactory;
		protected $customerRepositoryInterface;
		protected $customer;
		
		protected $fcSyncAccount;
		protected $fcSyncOrder;
		
		const PAGE_SIZE = 1000;
		
		const SF_CUSTOMER_ID_FIELD = 'sf_acctid';
		const SF_ORDER_ID_FIELD = 'sf_orderid';
		const SF_ORDER_ITEM_ID_FIELD = 'sf_order_itemid';
		const SF_LAST_SYNC_FIELD = 'sf_sync_date';

		/**
		 * AbstractCustomer constructor.
		 * @param Account $account
		 * @param CustomerRepositoryInterface $customerRepositoryInterface
		 */

		public function __construct(
			\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,
			\Magento\Sales\Api\OrderRepositoryInterface $orderRepositoryInterface,
			\Magento\Customer\Model\CustomerFactory $customerFactory,
			\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
			Account $fcSyncAccount,
			Order $fcSyncOrder
		) {
			$this->orderFactory = $orderFactory;
			$this->orderRepositoryInterface = $orderRepositoryInterface;
			$this->customerFactory = $customerFactory;
			$this->customerRepositoryInterface = $customerRepositoryInterface;
			
			$this->fcSyncAccount = $fcSyncAccount;
			$this->fcSyncOrder = $fcSyncOrder;
			
			parent::__construct($this->name);
		}

		/**
		 * {@inheritdoc}
		 */
		protected function configure()
		{
			$this->setName($this->name);
			$this->setDescription("Sync magento orders/customers to Salesforce");
			parent::configure();
		}
		
        /**
         * Execute the command
         *
         * @param InputInterface $input
         * @param OutputInterface $output
         *
         * @return null|int
         */
        protected function execute(InputInterface $input, OutputInterface $output)
        {
			echo "Sync started\n";
			
			// get recently modified customers
			$customers = $this->getCustomersCollection();
			
			echo $customers->getSize() . " customers found\n";
			
			$this->processCustomers($customers);
			
			// get recently modified orders
			$orders = $this->getOrderCollection();
			
			echo $orders->getSize() . " orders found\n";
			
			$this->processOrders($orders);
			
			echo "Sync completed\n";
        }
		
		protected function getOrderCollection()
		{
			return $this->orderFactory->create()
				->addAttributeToSelect('*')
				->addFieldToFilter("updated_at", array('gt' => $this->getUpdatedAtFilterDate()))
				->setPageSize(self::PAGE_SIZE)
				//->addFieldToFilter("sf_sync_date", array('gt' => '0'))
				->load();
		}
		
		protected function processOrders($orders)
		{
			// handle looping through large collections
			for($i=1; $i<= $this->getPageCount($orders); $i++)
			{
				$orders->setCurPage($i);
				
				foreach($orders as $order)
				{
					// load customer instance for updating
					$orderInstance = $this->orderRepositoryInterface->get($order->getId());
					
					echo "Sync " . $orderInstance->getIncrementId() . "\n";
					
					$sfOrderId = $this->fcSyncOrder->sync($order->getIncrementId());
					
					echo "sfOrderId = " . $sfOrderId . "\n";
					
					// new accounts return SF account id
					if($sfOrderId) {
						$orderInstance->setCustomAttribute(self::SF_ORDER_ID_FIELD, $sfOrderId);
					}
					
					// always update the last sync time
					$orderInstance->setCustomAttribute(self::SF_LAST_SYNC_FIELD, date("Y-m-d h:i:s"));
					
					$this->orderRepositoryInterface->save($orderInstance);
				}
			}
		}

		protected function getCustomersCollection()
		{
			return $this->customerFactory->create()->getCollection()
				->addAttributeToSelect("*")
				->addFieldToFilter("updated_at", array('gt' => $this->getUpdatedAtFilterDate()))
				->setPageSize(self::PAGE_SIZE)
				//->addAttributeToFilter("firstname", array("eq" => "Paul"))
				->load();
		}
		
		protected function processCustomers($customers)
		{
			// handle looping through large collections
			for($i=1; $i<= $this->getPageCount($customers); $i++)
			{
				$customers->setCurPage($i);
				
				foreach($customers as $customer)
				{
					// load customer instance for updating
					$customerInstance = $this->customerRepositoryInterface->getById($customer->getId());
					
					echo "Sync " . $customerInstance->getEmail() . "\n";
					
					$sfAccountId = $this->fcSyncAccount->sync($customer->getId());
					
					echo "sfAccountId = " . $sfAccountId . "\n";
					
					// new accounts return SF account id
					if($sfAccountId) {
						$customerInstance->setCustomAttribute(self::SF_CUSTOMER_ID_FIELD, $sfAccountId);
					}
					
					// always update the last sync time
					$customerInstance->setCustomAttribute(self::SF_LAST_SYNC_FIELD, date("Y-m-d h:i:s"));
					
					$this->customerRepositoryInterface->save($customerInstance);
				}
			}
		}
		
		protected function getPageCount($collection) {
			return ceil($collection->getSize() / self::PAGE_SIZE);
		}
		
		protected function getUpdatedAtFilterDate()
		{
			return '2020-12-01 00:00:00';
		}
	}