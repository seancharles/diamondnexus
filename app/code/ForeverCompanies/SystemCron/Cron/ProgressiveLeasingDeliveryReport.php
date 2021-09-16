<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;

class ProgressiveLeasingDeliveryReport
{
    protected $directory;
    protected $connection;
    protected $storeManager;
    protected $scopeConfig;
    protected $storeScope;
    protected $mailHelper;
    
    public function __construct(
        Filesystem $fileS,
        ResourceConnection $resourceC,
        StoreManagerInterface $storeManagerI,
        ScopeConfigInterface $scopeC,
        MailHelper $mailH
    ) {
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceC->getConnection();
        $this->storeManager = $storeManagerI;
        $this->scopeConfig = $scopeC;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->mailHelper = $mailH;
    }
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/report/progressive_leasing_delivery_report', $this->storeScope)) {
            return $this;
        }
        
        $date = date('Y-m-d', strtotime('yesterday'));
        
        $bodyHTML = null;
        
        $filename = '/var/www/magento/var/report/progressive-leasing-delivery-' . $date . '.csv';
        
        $stream = $this->directory->openFile($filename, 'w+');
        $stream->lock();
        
        $orderQuery = "SELECT
            o.store_id,
            o.increment_id,
            #o.lease_id,
            CONCAT(o.customer_firstname, ' ', o.customer_lastname) name,
            o.grand_total,
            o.status,
            MIN(h.created_at) delivery_date,
            o.created_at
            
            FROM sales_order o
            INNER JOIN sales_order_payment p ON o.entity_id = p.parent_id
            INNER JOIN sales_order_status_history h ON o.entity_id = h.parent_id
            WHERE p.multipay_payment_method = 7
            AND o.created_at > CURRENT_DATE - INTERVAL 180 DAY
            AND h.status IN('delivered')
            AND o.status IN('delivered')
            GROUP BY o.entity_id;";
        
        $orderResult = $this->connection->fetchAll($orderQuery);
        
        echo count($orderResult) . " rows found!\n";
        
        $stream->writeCsv(
            array(
                "Store Name",
                "Lease ID",
                "Client Name",
                "Invoice Amount",
                "Delivery Date"
            )
            );
        
        $bodyHTML .= '<table border="1">';
        $bodyHTML .= '<tr>';
        $bodyHTML .= '<th>Store Name</th>';
        $bodyHTML .= '<th>Lease ID </th>';
        $bodyHTML .= '<th>Client Name</th>';
        $bodyHTML .= '<th>Invoice Amount</th>';
        $bodyHTML .= '<th>Delivery Date</th>';
        $bodyHTML .= '<th>Order ID</th>';
        $bodyHTML .= '</tr>';
        
        foreach($orderResult as $order) {
            
            $storeName = "Admin";
            if( $order['store_id'] > 0 ) {
                $storeName = $this->storeManager->getStore($order['store_id'])->getName();
            }
            
            // TODO: Lease ID.
            $temp = array(
                'store_name' => $storeName,
                //'lease_id' => $order['lease_id'],
                'client_name' => $order['name'],
                'invoice_amount' => $order['grand_total'],
                'delivery_date' => date("m/d/y", strtotime($order['delivery_date']))
            );
            
            $stream->writeCsv(
                $temp
                );
            
            $report[] = $temp;
            
            $bodyHTML .= '<tr>';
            $bodyHTML .= '<td>'.$temp['store_name'].'</td>';
            // $bodyHTML .= '<td>'.$temp['lease_id'].'</td>';
            $bodyHTML .= '<td>'.$temp['client_name'].'</td>';
            $bodyHTML .= '<td>'.$temp['invoice_amount'].'</td>';
            $bodyHTML .= '<td>'.$temp['delivery_date'].'</td>';
            $bodyHTML .= '<td>'.$order['increment_id'].'</td>';
            $bodyHTML .= '</tr>';
        }
        
        $bodyHTML .= '</table>';
        
        
        
        $mail = new \Zend_Mail();
        $mail->setBodyHtml(
            "Daily Progressive Leasing Delivery Report - " . $date .
            "<br />" .
            "<br />" .
            $bodyHTML .
            "<br />" .
            "Forward spreadsheet with Lease Id column populated to readytofund@progleasing.com" .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" .
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
            "</span>"
            )
            ->setFrom('reports@forevercompanies.com', 'Forever Companies Reports')
            ->setReplyTo('no-reply@forevercompanies.com', 'No Reply')
            ->addTo('paul.baum@forevercompanies.com')
            ->addTo('accounting@forevercompanies.com')
            ->addTo('jessica.nelson@diamondnexus.com')
            ->setSubject('Daily Progressive Leasing Delivery Report - ' . $date . ((count($orderResult) == 0) ? ': No Orders Found' : '' ));
            
            
            $this->mailHelper->setFrom([
                'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/progressive_leasing_delivery_report/from_name',
                    $this->storeScope),
                'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/progressive_leasing_delivery_report/from_email',
                    $this->storeScope)
            ]);
            
            $this->mailHelper->addTo(
                $this->scopeConfig->getValue('forevercompanies_cron_schedules/progressive_leasing_delivery_report/to_email',
                    $this->storeScope),$this->scopeConfig->getValue('forevercompanies_cron_schedules/progressive_leasing_delivery_report/to_name',
                        $this->storeScope)
                );
            
            $content = file_get_contents($filename);
            
            $this->mailHelper->setSubject('Daily Progressive Leasing Delivery Report - ' . $date . ((count($orderResult) == 0) ? ': No Orders Found' : '' ));
            $this->mailHelper->setIsHtml(true);
            $this->mailHelper->setBody(
                "Daily Progressive Leasing Delivery Report - " . $date .
                "<br />" .
                "<br />" .
                $bodyHTML .
                "<br />" .
                "Forward spreadsheet with Lease Id column populated to readytofund@progleasing.com" .
                "<br />" .
                "<br />" .
                "<span style='font-size:10px;'>" .
                "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
                "</span>");
                $this->mailHelper->send(array("name" => $filename, "content" => $content));
            
            echo "Email sent!\n";
    }
}