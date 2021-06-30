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

use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection as OrderShipmentCollection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $logger;
	
	protected $orderCollectionFactory;	
	protected $userFactory;
	protected $directory;
	protected $connection;
	protected $storeManager;
	
	protected $orderShipmentCollection;
	protected $productFactory;
	protected $orderFactory;
	protected $websiteRepository;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    UserFactory $userF,
	    Filesystem $fileS,
	    ResourceConnection $resourceC,
	    StoreManagerInterface $storeManagerI,
	    OrderShipmentCollection $orderShipmentC,
	    ProductFactory $productF,
	    OrderFactory $orderF,
	    WebsiteRepositoryInterface $websiteRepositoryI
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		
		$this->userFactory = $userF;
		$this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
		
		$this->connection = $resourceC->getConnection();
		
		$this->storeManager = $storeManagerI;
		
		$this->orderShipmentCollection = $orderShipmentC;
		$this->productFactory = $productF;
		$this->orderFactory = $orderF;
		$this->websiteRepository = $websiteRepositoryI;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $date = date('Y-m-01', strtotime('now -1 month'));  // now -1 day
	    $enddate = date('Y-m-t', strtotime('now -1 month'));  // now -1 day
	    $fromDate = $date.' 00:00:00';
	    $toDate = $enddate.' 23:59:59';                                                  
	    
	    $filename = '/var/www/magento/var/report/sale-promo-disc__' . $date . '.csv';
	    
	    $stream = $this->directory->openFile($filename, 'w+');
	    $stream->lock();
	    
	    $collection = $this->orderShipmentCollection
	    ->addAttributeToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate));
	    
	    $stream->writeCsv(
	        array(
	            "Ship/Credit Date",
	            "Order #",
	            "SKU",
	            "Quantity",
	            "Unit List Price",
	            "Unit Discounted Price",
	            "Variance",
	            "Brand",
	            "Channel",
	            "Total Refunded",
	            "Type"
	        )
	    );
	    
	    
	    // get Current Store Name
	    $stores_list = $this->storeManager->getStores(true, true);
	    
	    
	    foreach ($collection as $shipment) {
	        $order = $this->orderFactory->create()->load($shipment->getOrderId());
	        foreach($order->getAllItems() as $item) {
	            $product = $this->productFactory->create()->load($item->getProductId());
	            
	            foreach ($stores_list as $storekey => $storevalue) {
	                if ($order->getStoreId() == $storevalue->getId()) {
	                   
	                    $store_name = $storevalue->getName();
	                    $website = $this->websiteRepository->getById($storevalue->getWebsiteId());
	                }
	            }
	            
	         
	            
	            if ((($product->getPrice() - $item->getPrice()) > 10) && ($item->getPrice() == $product->getSpecialPrice())) {
	                $stream->writeCsv(
	                    array(
	                        $shipment->getCreatedAt(),
	                        $order->getIncrementId(),
	                        $item->getSku(),
	                        $item->getQtyOrdered(),
	                        $product->getPrice(),
	                        $item->getPrice(),
	                        ($item->getPrice() - $product->getPrice()),
	                        $website->getName(),
	                        $store_name,
	                        (int)$order->getTotalRefunded(),
	                        "Sale/Promo"
	                    )
	                );
	            }
	            elseif($item->getOriginalPrice() != $item->getPrice() && $item->getOriginalPrice() > 0) {
	                $stream->writeCsv(
	                    array(
	                        $shipment->getCreatedAt(),
	                        $order->getIncrementId(),
	                        $item->getSku(),
	                        $item->getQtyOrdered(),
	                        $product->getPrice(),
	                        $item->getPrice(),
	                        ($item->getPrice() - $product->getPrice()),
	                        $website->getName(),
	                        $store_name,
	                        (int)$order->getTotalRefunded(),
	                        "Manual"
	                    )
	                );
	            }
	        }
	    }
	    
	    
	    $mail = new \Zend_Mail();
	    $mail->setBodyHtml("All Sale/Promo/Custom Disc Report - " . $date. " \r\n")
	    ->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
	    ->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
	    ->addTo('epasek@forevercompanies.com')
	    ->addTo('bill.tait@forevercompanies.com')
	    ->addTo('jessica.nelson@diamondnexus.com')
	    ->addTo('ken.licau@forevercompanies.com')
	    ->addTo('andrew.roberts@forevercompanies.com')
	    ->setSubject('Sale/Promo/Custom Disc Report - ' . $date);
	    
	    $content = file_get_contents($filename);
	    $attachment = new \Zend_Mime_Part($content);
	    $attachment->type = mime_content_type($filename);
	    $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
	    $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
	    $attachment->filename = 'sale-promo-disc_' . $date . '.csv';
	    
	    $mail->addAttachment($attachment);
	    $mail->send();
	}
}