<?php

namespace ForeverCompanies\SystemCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;


use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    
	protected $logger;
	
	protected $orderCollectionFactory;
	protected $customerCollectionFactory;
	protected $orderItemFactory;
	
	protected $userFactory;
	protected $directory;
	
	protected $transactionFactory;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    CustomerCollectionFactory $customerCollectionF,
	    UserFactory $userF,
	    Filesystem $fileS,
	    TransactionSearchResultInterfaceFactory $transactions,
	    OrderItemFactory $orderItemF
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		$this->customerCollectionFactory = $customerCollectionF;
		
		$this->transactionFactory = $transactions;
		$this->orderItemFactory = $orderItemF;
		
		$this->userFactory = $userF;
		$this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $date      = date('Y-m-01', strtotime('now -120 day'));  // now -1 day
	    $enddate      = date('Y-m-t', strtotime('now'));  // now -1 day
	    $fromDate = $date.' 00:00:00';
	    $toDate = $enddate.' 23:59:59';
	    
	    $filename = '/var/www/magento/var/report/ship_orders_' . $date . '.csv';
	    
	    
	    $stream = $this->directory->openFile($filename, 'w+');
	    $stream->lock();
	    
	    $order_collection = $this->orderCollectionFactory->create()
	    ->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
	    ->addFieldToFilter('status', array('in' => array('Shipped')));
	    
	    
	    $stream->writeCsv( 
	        array("Order Id", "Order Status", "Email", "Shipped Date")
	    );
	    
	    foreach ($order_collection as $order) {
	        
	        $sales_person = $this->userFactory->create()->load($order->getSalesPersonId())->getUserName();
	        
	        if (empty($sales_person)) {
	            $sales_person = 'Web';
	        }
	      
	        foreach($order->getShipmentsCollection() as $shipment){
	            
	            $shipped_date = $shipment->getCreatedAt();
	            
	            if (trim($shipped_date) != "") {
	                break;
	            }
	            
	        }
	        
	        if (isset($shipped_date) && (strtotime($shipped_date) < strtotime('now -4 day')) && (strtotime($shipped_date) > strtotime('now -6 day'))) {
	            #print $order->increment_id." ".$order->status." ".$order->customer_email." ".$shipped_date."\n";
	            
	            $stream->writeCsv( 
	                array(
	                    $order->getIncrementId(),
	                    $order->getStatus(),
	                    $order->getCustomerEmail(),
	                    $shipped_date
	                )
	            );
	        }
	        
	    }
	}
}