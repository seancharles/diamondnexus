<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;

class OpenOrderReport
{
    protected $orderCollectionFactory;
    protected $userFactory;
    protected $directory;
    protected $transactionFactory;
    protected $scopeConfig;
    protected $storeScope;
    protected $maiHelper;
    
    public function __construct(
        OrderCollectionFactory $orderCollectionF,
        UserFactory $userF,
        Filesystem $fileS,
        ScopeConfigInterface $scopeC,
        MailHelper $mailH
    ) {
        $this->orderCollectionFactory = $orderCollectionF;
        $this->userFactory = $userF;
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->mailHelper = $mailH;
    }
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/report/open_order_report', $this->storeScope)) {
            return $this;
        }
        
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
        
        $stream->writeCsv(
            array(
                "Order Id",
                "Order Date",
                "Order Status",
                "Email",
                "Sub Total",
                "Total Due",
                "Sales Person",
                "Total Refunded",
                "Shipping Amount",
                "Discount Amount"
            )
        );
        
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
        
        
        $this->mailHelper->setFrom([
            'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/open_order_report/from_name',
                $this->storeScope),
            'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/open_order_report/from_email',
                $this->storeScope)
        ]);
        
        $this->mailHelper->addTo(
            $this->scopeConfig->getValue('forevercompanies_cron_schedules/open_order_report/to_email',
            $this->storeScope),$this->scopeConfig->getValue('forevercompanies_cron_schedules/open_order_report/to_name',
                $this->storeScope)
        );
        
        
        $content = file_get_contents($filename);
        
        $this->mailHelper->setSubject('Open Orders Report - ' . $date);
        $this->mailHelper->setIsHtml(true);
        $this->mailHelper->setBody("All Open Orders Report - " . $date. " \r\n");
        $this->mailHelper->send(array("name" => $filename, "content" => $content));
    }
}