<?php

namespace ForeverCompanies\SystemCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $logger;
	
	protected $orderCollectionFactory;
	protected $userFactory;
	protected $directory;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    UserFactory $userF,
	    Filesystem $fileS
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		$this->userFactory = $userF;
		$this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    // Date
	    $date      = date('Y-m-d', strtotime('now -20 day'));  // now -1 day
	    $fromDate = $date.' 06:00:00';
	    $tosdate      = date('Y-m-d', strtotime('now'));  // now -1 day
	    $toDate = $tosdate.' 06:00:00';
	    
	    $filename = '/var/www/magento/var/report/sp_' . $date . '.csv';
	    
	    $order_collection = $this->orderCollectionFactory->create()
	    ->addAttributeToFilter('updated_at', array('from' => $fromDate, 'to' =>    $toDate))
	    ->load();
	    
	    
	    $report[0] = array("Order Id", "Sales Person","Email");
	    $stream = $this->directory->openFile($filename, 'w+');
	    $stream->lock();
	    foreach ($order_collection as $order) {
	        
	        $sales_person = $this->userFactory->create()->load($order->getSalesPersonId())->getUserName();
	        
	        if (empty($sales_person)) {
	            $sales_person = 'Web';
	        }
	        $stream->writeCsv(array($order->getIncrementId(), $sales_person, $order->getCustomerEmail()));
	    }
	    
	    
	    $mail = new \Zend_Mail();
	    $mail->setBodyHtml("All Sales Person Report - " . $date. " \r\n")
	    ->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
	    ->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
	    ->addTo('epasek@forevercompanies.com')
	    ->addTo('bill.tait@forevercompanies.com')
	    ->addTo('jessica.nelson@diamondnexus.com')
	    ->addTo('ken.licau@forevercompanies.com')
	    ->addTo('andrew.roberts@forevercompanies.com')
	    ->addTo('mitch.stark@forevercompanies.com')
	    ->setSubject('Sales Person Report - ' . $date);
	    
	    $content = file_get_contents($filename);
	    $attachment = new \Zend_Mime_Part($content);
	    $attachment->type = mime_content_type($filename);
	    $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
	    $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
	    $attachment->filename = 'sp_' . $date . '.csv';
	    
	    $mail->addAttachment($attachment);
	    
	    $mail->send();
	}
}