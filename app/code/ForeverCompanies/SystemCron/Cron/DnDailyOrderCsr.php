<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;

class DnDailyOrderCsr
{   
    protected $directory;
    protected $connection;
    
    public function __construct(
        Filesystem $fileS,
        ResourceConnection $resourceC
    ) {
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceC->getConnection();
    }
    
    public function execute()
    {
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
        
        $mail = new \Zend_Mail();
        $mail->setBodyHtml(
            "Daily CSR Report - " . $date .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" .
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
            "</span>"
            )
            ->setFrom('reports@forevercompanies.com', 'Forever Companies Reports')
            ->setReplyTo('paul.baum@forevercompanies.com', 'Paul Baum')
            ->addTo('jessica.nelson@diamondnexus.com')
            ->addTo('paul.baum@forevercompanies.com')
            ->setSubject('Daily CSR Report - ' . $date . ( (count($orderResult) == 0) ? ': No Orders Found' : '' ));
            
        $content = file_get_contents($filename);
        $attachment = new \Zend_Mime_Part($content);
        $attachment->type = mime_content_type($filename);
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = 'cr-' . $date . '.csv';
        
        if( count($orderResult) > 0 ) {
            $mail->addAttachment($attachment);
        }
        
        $mail->send();
        echo "Email sent!\n";
    }
    
}