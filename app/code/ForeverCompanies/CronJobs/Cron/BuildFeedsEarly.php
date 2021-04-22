<?php

namespace ForeverCompanies\CronJobs\Cron;

use ForeverCompanies\CronJobs\Model\FeedLogic;

class BuildFeedsarly
{
    protected $feedModel;

    public function __construct(
        FeedLogic $feed
        ) {
        $this->feedModel = $feed;
    }

    public function execute() 
    {
        // TODO Schedules are currently hard-coded but can be moved into config at a later date.
       
        $this->feedModel->BuildCsvs(1);
        $this->feedModel->BuildCsvs(12);
        return;
        // make a @to-do note in the module somewhere stating that schedule is hard coded and we could add admin config for it.
        /*
            
            /usr/local/bin/php /var/www/magento/app/code/ForeverCompanies/CronJobs/Cron/BuildFeed.php CSV 1
         
         
            19.1.0. Diamond Nexus Feeds
            1. 00 9 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php CSV 1
            2. 00 9 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php criteoCSV 1
            3. 00 18 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php CSV 1
            4. 00 2 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php updateReviews 1
            5. 00 2 * * 0 /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php createReviews 1
            
            19.1.1. 1215 Feeds
            1. 00 9 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php CSV 12
            2. 00 18 * * * /usr/bin/php /home/admin/html/shell/dnl/google_api/build_feed.php CSV 12
            
         */
        
        $argvStoreId = ($argv[2]) ?: 1;
        
        $argv[1] = "CSV";
        
        
        $this->feedModel->BuildCsvs(1);
        
        
        return;
        
        switch ($argv[1]) {
            case 'status':
                $this->doCheckStatus();
                break;
            case 'single':
                if(!isset($argv[3])) {
                    throw new Exception('Product ID must be defined');
                }
                $listing = $this->getOneProduct($argv[3]);
                $this->doFeed($listing);
                break;
            case 'checksingle':
                if(!isset($argv[3])) {
                    throw new Exception('Product ID must be defined');
                }
                $listing = $this->getOneProduct($argv[3]);
                print_r($listing);
                #doCheckFeed($listing);
                break;
            case 'all':
                $listing = $this->getAllProductList();
                $this->createCSV($listing);
                $this->createYahooCSV($listing);
                $this->createFBCSV($listing);
                $this->doFeed($listing);
                $this->cleanFeed();
                break;
            case 'API':
                $listing = $this->getAllProductList();
                $this->doFeed($listing);
                $this->cleanFeed();
                break;
            case 'check':
                $listing = $this->getAllProductList();
                $this->doCheckFeed($listing);
                break;
            case 'clean':
                $this->cleanFeed();
                break;
            case 'update':
                $listing = $this->getRecentProductList();
                $this->doFeed($listing);
                $this->cleanFeed();
                break;
            case 'createReviews':
                $this->getProductsReviews('full');
                break;
            case 'updateReviews':
                $this->getProductsReviews('inc');
                break;
            case 'accountInfo':
                $this->getAccountInfo();
                break;
            case 'CSV':
                $listing = $this->getAllProductList();
                $this->createCSV($listing);
                $this->createYahooCSV($listing);
                $this->createFBCSV($listing);
                break;
            case 'FB':
                $listing = $this->getAllProductList();
                $this->createFBCSV($listing);
                break;
            default:
                $this->usage();
                break;
        }
    }
    
    
    
    
}