<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class OpenOrderReport
{
    protected $orderCollectionFactory;
    
    protected $userFactory;
    protected $directory;
    
    protected $transactionFactory;
    
    public function __construct(
        OrderCollectionFactory $orderCollectionF,
        UserFactory $userF,
        Filesystem $fileS
        ) {
            $this->orderCollectionFactory = $orderCollectionF;
            
            
            $this->userFactory = $userF;
            $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
            
    }
    
    public function execute()
    {
        $date      = date('Y-m-01', strtotime('now -1 month'));  // now -1 day
        $enddate      = date('Y-m-t', strtotime('now -1 month'));  // now -1 day
        $fromDate = $date.' 00:00:00';
        $toDate = $enddate.' 23:59:59';
        
        $filename = '/var/www/magento/var/report/open_orders_' . $date . '.csv';
        
        $stream = $this->directory->openFile($filename, 'w+');
        $stream->lock();
        
        $order_collection = $this->orderCollectionFactory->create()
        ->addAttributeToFilter('updated_at', array('from'=>$fromDate, 'to'=>$toDate))
        ->addFieldToFilter('status', array('in' => array('Processing', 'Pending')));
        
        $report[0] = array("Order Id","Order Date", "Order Status", "Email", "Sub Total", "Total Due", "Sales Person", "Total Refunded","Shipping Amount","Discount Amount");
        
        foreach ($order_collection as $order) {
            $sales_person = $this->userFactory->create()->load($order->getSalesPersonId())->getUserName();
            if (empty($sales_person)) {
                $sales_person = 'Web';
            }
            $stream->writeCsv(
                array(
                    $order->getIncrementId(),
                    $order->getCreatedAt(),
                    $order->getStatus(),
                    $order->getCustomerEmail(),
                    $order->getSubtotal(),
                    $order->getTotalDue(),
                    $sales_person,
                    $order->getTotalRefunded(),
                    $order->getShippingAmount(),
                    $order->getDiscountAmount()
                )
                );
        }
        
        $mail = new \Zend_Mail();
        $mail->setBodyHtml("All Open Orders Report - " . $date. " \r\n")
        ->setFrom('it@diamondnexus.com', 'Diamond Nexus Reports')
        ->setReplyTo('epasek@forevercompanies.com', 'Edie Pasek')
        ->addTo('epasek@forevercompanies.com')
        ->addTo('ken.licau@forevercompanies.com')
        ->setSubject('Open Orders Report - ' . $date);
        
        $content = file_get_contents($filename);
        $attachment = new \Zend_Mime_Part($content);
        $attachment->type = mime_content_type($filename);
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = 'open_orders_' . $date . '.csv';
        
        $mail->addAttachment($attachment);
        
        $mail->send();
    }
}