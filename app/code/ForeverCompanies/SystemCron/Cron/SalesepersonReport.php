<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SalespersonReport
{
    protected $orderCollectionFactory;
    protected $userFactory;
    protected $directory;
    protected $scopeConfig;
    protected $storeScope;
    
    public function __construct(
        OrderCollectionFactory $orderCollectionF,
        UserFactory $userF,
        Filesystem $fileS,
        ScopeConfigInterface $scopeC
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionF;
        $this->userFactory = $userF;
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/report/salesperson_report', $this->storeScope)) {
            return $this;
        }
        
        // Date
        $date      = date('Y-m-d', strtotime('now -20 day'));  // now -1 day
        $fromDate = $date.' 06:00:00';
        $tosdate      = date('Y-m-d', strtotime('now'));  // now -1 day
        $toDate = $tosdate.' 06:00:00';
        
        $filename = '/var/www/magento/var/report/sp_' . $date . '.csv';
        
        $order_collection = $this->orderCollectionFactory->create()
        ->addAttributeToFilter('updated_at', array('from' => $fromDate, 'to' => $toDate))
        ->load();
        
        $stream->writeCsv(
            array("Order Id", "Sales Person","Email")
        );
        
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