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

		protected $orderCollectionFactory;
		protected $customerFactory;
		
		protected $fcSyncAccount;
		protected $fcSyncOrder;
		
		const PAGE_SIZE = 1000;

		/**
		 * AbstractCustomer constructor.
		 * @param Account $account
		 * @param CustomerRepositoryInterface $customerRepositoryInterface
		 */

		public function __construct(
			\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
			\Magento\Customer\Model\CustomerFactory $customerFactory,
			Account $fcSyncAccount,
			Order $fcSyncOrder
		) {
			$this->orderCollectionFactory = $orderCollectionFactory;
			$this->customerFactory = $customerFactory;
			
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
			
			// get recently modified orders
			$orders = $this->getOrderCollection();
			
			echo $orders->getSize() . " orders found\n";
			
			$this->processOrders($orders);
			
			// get recently modified customers
			$customers = $this->getCustomersCollection();
			
			echo $orders->getSize() . " customers found\n";
			
			$this->processCustomers($orders);
			
			echo "Sync completed\n";
        }
		
		protected function getOrderCollection()
		{
			return $this->orderCollectionFactory->create()
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
					echo "Sync order " . $order->getIncrementId() . "\n";
					
					
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
					echo "Sync customer " . $customer->getId() . "\n";
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