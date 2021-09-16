<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;

class SalespersonReport
{
    protected $orderCollectionFactory;
    protected $userFactory;
    protected $directory;
    protected $scopeConfig;
    protected $storeScope;
    protected $mailHelper;
    
    public function __construct(
        OrderCollectionFactory $orderCollectionF,
        UserFactory $userF,
        Filesystem $fileS,
        ScopeConfigInterface $scopeC,
        MailHelper $mailH
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionF;
        $this->userFactory = $userF;
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->mailHelper = $mailH;
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
        
        $this->mailHelper->setFrom([
            'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/salesperson_report/from_name',
                $this->storeScope),
            'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/salesperson_report/from_email',
                $this->storeScope)
        ]);
        
        $this->mailHelper->addTo(
            $this->scopeConfig->getValue('forevercompanies_cron_schedules/salesperson_report/to_email',
                $this->storeScope),$this->scopeConfig->getValue('forevercompanies_cron_schedules/salesperson_report/to_name',
                    $this->storeScope)
            );
        
        $content = file_get_contents($filename);
        
        $this->mailHelper->setSubject('Sales Person Report - ' . $date);
        $this->mailHelper->setIsHtml(true);
        $this->mailHelper->setBody("All Sales Person Report - " . $date. " \r\n");
        $this->mailHelper->send(array("name" => $filename, "content" => $content));
    }
}