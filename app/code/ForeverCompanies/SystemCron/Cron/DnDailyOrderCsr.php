<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DnDailyOrderCsr
{   
    protected $directory;
    protected $connection;
    protected $scopeConfig;
    protected $storeScope;
    protected $mailHelper;
    
    public function __construct(
        Filesystem $fileS,
        ResourceConnection $resourceC,
        ScopeConfigInterface $scopeC,
        MailHelper $mailH
    ) {
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceC->getConnection();
        $this->scopeConfig = $scopeC;
        $this->mailHelper = $mailH;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/report/dn_daily_order_csr', $this->storeScope)) {
            return $this;
        }
        
        $date = date('Y-m-d', strtotime('now -30 day'));  // now -30 day
        $tosdate = date('Y-m-d', strtotime('now'));  // now -1 day
        
        $fromDate = $date.' 00:00:00';
        $toDate = $tosdate.' 23:59:59';
        
        $filename = '/var/www/magento/var/report/csr-' . $date . '.csv';
        
        $stream = $this->directory->openFile($filename, 'w+');
        $stream->lock();
        
        $orderQuery = "
            SELECT
            so.increment_id,
            CONCAT(a.firstname,' ', a.lastname) sales_rep,
            so.status,
            CONCAT(so.customer_firstname,' ',so.customer_lastname) customer_name,
            so.customer_email,
            ba.telephone billing_phone,
            so.created_at,
            so.grand_total
            FROM
                sales_order so
            LEFT JOIN
                admin_user a ON so.sales_person_id = a.user_id
            LEFT JOIN
                sales_order_address ba ON so.billing_address_id = ba.entity_id
            WHERE
                so.created_at BETWEEN ' 00:00:00' AND ' 23:59:59'
            AND
                so.status NOT IN('canceled')
            AND
                so.store_id = 5
            ORDER BY
                increment_id DESC
        ";
        
        echo $orderQuery . "\n";
        
        $orderResult = $this->connection->fetchAll($orderQuery);
        
        echo count($orderResult) . " rows found!\n";
        
        $stream->writeCsv(
            array(
                "Order Id",
                "Sales Rep",
                "Status",
                "Name",
                "Email",
                "Phone",
                "Date",
                "Grand Total"
            )
        );
        
        foreach($orderResult as $order) {
            $stream->writeCsv(
                array(
                    $order['increment_id'],
                    $order['sales_rep'],
                    $order['status'],
                    $order['customer_name'],
                    $order['customer_email'],
                    $order['billing_phone'],
                    $order['created_at'],
                    $order['grand_total']
                )
            );
        }
        
        $this->mailHelper->setFrom([
            'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_order_csr/from_name',
                $this->storeScope),
            'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_order_csr/from_email',
                $this->storeScope)
        ]);
        
        $this->mailHelper->addTo(
            $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_order_csr/to_email',
                $this->storeScope),$this->scopeConfig->getValue('dn_daily_order_csr/dn_daily_order_csr/to_name',
                    $this->storeScope)
        );
         
        $content = file_get_contents($filename);
            
        $this->mailHelper->setSubject('Daily CSR Report - ' . $date . ( (count($orderResult) == 0) ? ': No Orders Found' : '' ));
        $this->mailHelper->setIsHtml(true);
        $this->mailHelper->setBody(
            "Daily CSR Report - " . $date .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" .
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
            "</span>");
        $this->mailHelper->send(array("name" => $filename, "content" => $content));
        
        echo "Email sent!\n";
    }
    
}