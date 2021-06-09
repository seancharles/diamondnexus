<?php

namespace ForeverCompanies\SystemCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $logger;
	
	protected $orderCollectionFactory;	
	protected $userFactory;
	protected $directory;
	protected $connection;
	protected $storeManager;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    UserFactory $userF,
	    Filesystem $fileS,
	    ResourceConnection $resourceC,
	    StoreManagerInterface $storeManagerI
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		
		$this->userFactory = $userF;
		$this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
		
		$this->connection = $resourceC->getConnection();
		
		$this->storeManager = $storeManagerI;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    
	    $count_catalogs = 0;
	    $count_product_catalogs = 0;
	    $count_engagement_catalog = 0;
	    $count_custom_catalog = 0;
	    
	    // Date
	    $start_date = date('Y-m-d',strtotime('now -1 day'));
	    $end_date = date('Y-m-d',strtotime('now -1 day'));
	    
	    
	    $query = "
        	SELECT
        		first_name,
        		last_name,
        		address_1 as address1,
        		address_2 as address2,
        		city,
        		region as state,
        		postal_code as zip,
        		country_id as country,
        		send_product_catalog,
        		send_engagement_catalog,
        		send_custom_catalog
        	FROM
        		visitor_submissions
        	WHERE
        		submitted_at between '".$start_date." 00:00:00' AND '".$end_date." 23:59:59'
        	AND
        	(
        		send_product_catalog = '1'
        	OR
        		send_engagement_catalog = '1'
        	OR
        		send_custom_catalog = '1'
        	);
        ";
	    
	    echo $query;die;
	    
	    
	    
	}
}