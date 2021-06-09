<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;

class DnDailyCatalogRequests
{   
    protected $directory;
    protected $connection;
    
    public function __construct(
        Filesystem $fileS,
        ResourceConnection $resource
    ) {
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceC->getConnection();
    }
    
    public function execute()
    {
        $tosdate = date('Y-m-d', strtotime('now'));  // now -1 day
        $date = date('Y-m-d', strtotime('yesterday'));
        
        $fromDate = $date.' 06:00:00';
        $toDate = $tosdate.' 06:00:00';
        
        $filename = '/var/www/magento/var/report/cr-' . $date . '.csv';
        
        $stream = $this->directory->openFile($filename, 'w+');
        $stream->lock();
        
        $catalogQuery = "SELECT
            * FROM fc_form_submission
            WHERE created_at BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
            AND form_id = 5
            ORDER BY created_at ASC;";
        
        echo $catalogQuery . "\n";
        
        $leadsResult = $this->connection->fetchAll($catalogQuery);
        
        $stream->writeCsv(
            array(
                "Email Address",
                "Phone",
                "Engagement Ring Shopping",
                "Need By",
                "Type Need",
                "Items of Interest",
                
                "Gender",
                "First Name",
                "Last Name",
                "Address",
                "City",
                "Region",
                "Postal Code",
                "Country",
                
                "Date",
                "Slider Source",
                "Referer"
            )
        );
        
        foreach($leadsResult as $lead) {
            $stream->writeCsv(
                array(
                    $lead['email_address'],
                    $lead['phone_number'],
                    
                    $lead['engagement_ring'],
                    $lead['need_by'],
                    $lead['type_need'],
                    $lead['items_of_interest'],
                    $lead['info_gender'],
                    
                    $lead['first_name'],
                    $lead['last_name'],
                    $lead['address_1'],
                    $lead['city'],
                    $lead['region'],
                    $lead['postal_code'],
                    $lead['country'],
                    
                    $lead['submitted_at'],
                    $lead['slider_source'],
                    $lead['http_referer']
                )
            );
        }
        
        $mail = new \Zend_Mail();
        $mail->setBodyHtml(
            "Daily Catalog Request Report - " . $date .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" .
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
            "</span>"
        )
        ->setFrom('reports@forevercompanies.com', 'Forever Companies Reports')
        ->setReplyTo('paul.baum@forevercompanies.com', 'Paul Baum')
        ->addTo('mike.yarbrough@diamondnexus.com')
        ->addTo('tyler.kaminski@diamondnexus.com')
        ->addTo('jessica.nelson@diamondnexus.com')
        ->addTo('paul.baum@forevercompanies.com')
        ->setSubject('Daily Catalog Request Report - ' . $date . ( (count($leadsResult) == 0) ? ': No Leads Submitted' : '' ) );
        
        $content = file_get_contents($filename);
        $attachment = new \Zend_Mime_Part($content);
        $attachment->type = mime_content_type($filename);
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = \Zend_Mime::ENCODING_BASE64;
        $attachment->filename = 'cr-' . $date . '.csv';
        
        if( count($leadsResult) > 0 ) {
            $mail->addAttachment($attachment);
        }
        
        $mail->send();
        echo "Email sent!\n";
    }
}