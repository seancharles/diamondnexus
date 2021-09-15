<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DnDailyCatalogRequests
{   
    protected $directory;
    protected $connection;
    protected $scopeConfig;
    protected $storeScope;
    protected $mailHelper;
    
    public function __construct(
        Filesystem $fileS,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfigI,
        MailHelper $mailH
    ) {
        $this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->connection = $resourceC->getConnection();
        $this->mailHelper = $mailH;
        $this->scopeConfig = $scopeConfigI;
        $this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
    
    public function execute()
    {
        if (!$this->scopeConfig->getValue('forevercompanies_cron_controls/report/dn_daily_catalog_requests', $this->storeScope)) {
            return $this;
        }
        
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
        
        $this->mailHelper->setFrom([
            'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_catalog_requests/from_name',
                $this->storeScope),
            'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_catalog_requests/from_email',
                $this->storeScope)
        ]);
        
        $this->mailHelper->addTo(
            $this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_catalog_requests/to_email',
                $this->storeScope),$this->scopeConfig->getValue('forevercompanies_cron_schedules/dn_daily_catalog_requests/to_name',
                    $this->storeScope)
            );
        
        // TODO Add attachment capability to Mail Helper
        
        $content = file_get_contents($filename);
        
        $this->mailHelper->setSubject('Daily Catalog Request Report - ' . $date . ( (count($leadsResult) == 0) ? ': No Leads Submitted' : '' ));
        $this->mailHelper->setIsHtml(true);
        $this->mailHelper->setBody("Daily Catalog Request Report - " . $date .
            "<br />" .
            "<br />" .
            "<span style='font-size:10px;'>" .
            "Sent From <strong>@mag4:" . $_SERVER['PWD'] . "/" . $_SERVER['SCRIPT_FILENAME'] . "</strong>" .
            "</span>");
        $this->mailHelper->send(array("name" => $filename, "content" => $content));
        
        echo "Email sent!\n";
    }
}