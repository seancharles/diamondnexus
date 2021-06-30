<?php

 //   require_once $_SERVER['HOME'] . 'magento//Mage.php';
    
    Mage::app();
    
    class QuoteCleanup
    {
        public $debug;
        
        public function __construct($debug = false)
        {
            $this->debug = $debug;
            
            $this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        }
        
        /*
         * Pull orders 90 days old
         */
        public function getQuoteBandist()
        {
            // GMT adjustment (current timestamp is always +6 hours from db values)
            $adjustment = 60 * 60 * 6;
            
            // curent timestamp
            $time = time() - $adjustment;

            // how far back to look at orders (30 days)
            $maxDate = date('Y-m-d 00:00:00', $time - (86400 * 30) );
            
            $ordersQuery = "SELECT
                        entity_id
                    FROM
                        sales_flat_order
                    WHERE
                        status IN('Quote')
                    AND
                        created_at < '" . $maxDate . "'
                    ORDER BY
                        entity_id DESC;\n\n";
            
            if( $this->debug )            
                echo $ordersQuery;
            
            $orderList = $this->readConnection->fetchAll($ordersQuery);
			
            return $orderList;
        }
    }