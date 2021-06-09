<?php
namespace ForeverCompanies\SystemCron\Cron;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;

class CatalogLabelReport
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
        // TODO: Once you know how fc_form_submission will be populated do this.
        return;
        
        $count_catalogs = 0;
        $count_product_catalogs = 0;
        $count_engagement_catalog = 0;
        $count_custom_catalog = 0;
        
        // Date
        $start_date = date('Y-m-d',strtotime('now -1 day'));
        $end_date = date('Y-m-d',strtotime('now -1 day'));
        
        
        $query = "
        	SELECT
        		first_name,
        		last_name,
        		address_1 as address1,
        		address_2 as address2,
        		city,
        		region as state,
        		postal_code as zip,
        		country_id as country,
        		send_product_catalog,
        		send_engagement_catalog,
        		send_custom_catalog
        	FROM
        		visitor_submissions
        	WHERE
        		submitted_at between '".$start_date." 00:00:00' AND '".$end_date." 23:59:59'
        	AND
        	(
        		send_product_catalog = '1'
        	OR
        		send_engagement_catalog = '1'
        	OR
        		send_custom_catalog = '1'
        	);
        ";
        
        echo $query;die;
        
        
        
    }
    
}