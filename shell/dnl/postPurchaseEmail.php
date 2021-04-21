<?php
    # Pull all orders that are four days old and check if the order has an ASD of greater than five days
    # Order must be in processing (diamond nexus orders only)
    # Send post purchase email to customer

    # Create new table that will track post purchase emails
    # If someone has recieved an email for post purchase or it is failing

    # We will run the cron once an hour and create a check alert to report any emails that didn't get sent out
    # Emails that fail will not be sent out infinitely later. After the fourth day emails will no longer attempt to send

    require_once $_SERVER['HOME'] . '/html/app/Mage.php';
    
    Mage::app();
    
    class PostPurchase
    {
        const InstagramTemplateId = 70;
        const FinishingTemplateId = 72;
        
        public $debug;
        
        public function __construct($debug = false)
        {
            $this->debug = $debug;
            
            $this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
            $this->emailTransactionModel = Mage::getSingleton('postpurchase/email');
        }
        
        /*
         * Pull orders with date diff >= 5
         * Send them the instagram email on day three.
         */
        public function getInstagramList()
        {
            // GMT adjustment (current timestamp is always +6 hours from db values)
            $adjustment = 60 * 60 * 6;
            
            // curent timestamp
            $time = time() - $adjustment;

            $stamp = $time - 86400 * 3;

            $minDate = date('Y-m-d 00:00:00', $stamp);
            $maxDate = date('Y-m-d 18:59:59', $stamp);
            
            $ordersQuery = "SELECT
                        datediff(o.anticipated_shipdate, o.created_at) diff,
                        o.entity_id,
                        o.increment_id,
                        o.state,
                        o.customer_email,
                        o.status,
                        o.created_at,
                        o.anticipated_shipdate,
                        o.store_id,
                        e.sent,
                        e.failed
                    FROM
                        sales_flat_order o
                    LEFT JOIN
                        diamondnexus_postpurchase_email e ON o.entity_id = e.order_id
                    WHERE
                        o.status IN('processing','shipped','delivered','shipped')
                    AND
                        (o.total_due IS NULL OR o.total_due = 0)
                    AND
                        o.created_at  BETWEEN '" . $minDate . "' AND '" . $maxDate . "'
                    AND
                        o.store_id IN(1, 5)
                    AND
                        datediff(o.anticipated_shipdate, o.created_at) >= 5
                    AND
                        o.created_at > '2018-09-01 00:00:00'
                    ORDER BY
                        o.entity_id DESC;";
            
            if( $this->debug )
                echo $ordersQuery . "\n";
            
            $orderList = $this->readConnection->fetchAll($ordersQuery);
            
            return $orderList;
        }
        
        public function sendEmail($orderList = null, $templateId = 0)
        {
            echo "sendEmail\n";
            
            foreach($orderList as $order) {

                // load the order object to pass to the email
                $orderModel = Mage::getModel('sales/order')->load($order['entity_id']);
            
                $emailQuery = "SELECT * FROM diamondnexus_postpurchase_email WHERE order_id = '" . $orderModel->getId() ."' AND template_id = '" . $templateId . "';";
                
                if( $this->debug )
                    echo $emailQuery . "\n";
                
                $emailLog = $this->readConnection->fetchAll($emailQuery);

                // check if we found any logs for this email
                if( $orderModel->getId() == $order['entity_id'] && count($emailLog) == 0 || ($emailLog[0]['sent'] == 0 && $emailLog[0]['failed'] <= 5) )
                {
                    // we reset error to false for every item
                    $error = false;

                    echo "Sending email to " . $orderModel->getCustomerEmail() . "...\n";

                    // Set sender information
                    $senderName = Mage::getStoreConfig('trans_email/ident_support/name');
                    $senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');

                    $sender = array(
                        'name' => $senderName,
                        'email' => $senderEmail
                    );

                    $_ddTimestamp = strtotime( $orderModel->getData('delivery_date') );

                    // Set variables that can be used in email template
                    $vars = array(
                        'deliveryDateAvailable' => (boolean) $_ddTimestamp,
                        'deliveryDate' => $orderModel->getData('delivery_date'),
                        'deliveryDateDayOfWeek' => date('l', $_ddTimestamp),
                        'deliveryDateDayOfMonth' => date('j', $_ddTimestamp),
                        'deliveryDateMonth' => date('F', $_ddTimestamp),
                        'order' => $orderModel
                    );

                    $translate  = Mage::getSingleton('core/translate');

                    //print_r($order);
                    
                    try {
                        // Send Transactional Email
                        Mage::getModel('core/email_template')->sendTransactional(
                            $templateId,
                            $sender,
                            $orderModel->getCustomerEmail(),
                            //'paul.baum@forevercompanies.com',
                            $orderModel->getCustomerFirstname() . " " . $orderModel->getCustomerLastname(),
                            $vars,
                            $orderModel->getStoreId()
                        );

                        $translate->setTranslateInline(true);
                    }
                    catch (Exception $e) {

                        print($e->getMessage());

                        // this will log and increment the failed attempts number
                        $error = true;

                    } finally {
                    
                        $fields = array(
                            'order_id' => $orderModel->getId(),
                            'template_id' => $templateId,
                            'created_at' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()))
                        );
                        
                        if ($order['email_id']) {
                            $fields['email_id'] = $order['email_id'];
                        }

                        // add failure or success flag
                        if ( $error == true ) {
                            $fields['failed'] = $order['failed'] +1;
                        } else {
                            $fields['sent'] = 1;
                        }
                        
                        $this->emailTransactionModel->setData($fields);
                        $this->emailTransactionModel->save();
                    }
                }
            }
        }
        
        /*
         * Pull orders with date diff >= 5
         * Send them the instagram email on day three.
         */
        public function getFinishingList()
        {
            // GMT adjustment (current timestamp is always +6 hours from db values)
            $adjustment = 60 * 60 * 6;
            
            // curent timestamp
            $time = time() - $adjustment;

            // current date
            $date = date('Y-m-d 00:00:00', $time);

            // how far back to look at orders (25 days)
            $minDate = date('Y-m-d 00:00:00', $time - (86400 * 25) );
            
            $ordersQuery = "SELECT
                        datediff(o.anticipated_shipdate, o.created_at) diff,
                        datediff(o.anticipated_shipdate, '" . $date . "') diff2,
                        o.entity_id,
                        o.increment_id,
                        o.state,
                        o.customer_email,
                        o.status,
                        o.created_at,
                        o.anticipated_shipdate,
                        o.store_id
                    FROM
                        sales_flat_order o
                    WHERE
                        o.status IN('processing','shipped','delivered','shipped')
                    AND
                        (o.total_due IS NULL OR o.total_due = 0)
                    AND
                        datediff(o.anticipated_shipdate, '" . $date . "') = 2
                    AND
                        o.store_id IN(1, 5)
                    AND
                        datediff(o.anticipated_shipdate, o.created_at) >= 5
                    AND
                        o.created_at > '" . $minDate . "'
                    AND
                        o.created_at > '2018-09-01 00:00:00'
                    ORDER BY
                        o.entity_id DESC;\n\n";
            
            if( $this->debug )            
                echo $ordersQuery;
            
            $orderList = $this->readConnection->fetchAll($ordersQuery);
			
            return $orderList;
        }
    }
    
    $enableDebug = true;
    
    $postPurchase = new PostPurchase($enableDebug);
    
    echo "getting instagram list\n";
    
    $instagramList = $postPurchase->getInstagramList();
    
    if( count($instagramList) > 0 )
    {
        echo "sending instagram list\n";
        
        $postPurchase->sendEmail($instagramList, PostPurchase::InstagramTemplateId);
        
    } else {
        
        echo "no instagram emails found\n";
    }
    
    echo "getting finishing list\n";
    
    $finishingList = $postPurchase->getFinishingList();
    
    if( count($finishingList) > 0 )
    {
        echo "sending finishing list\n";
        
        //$postPurchase->sendEmail($finishingList, PostPurchase::FinishingTemplateId);
        
    } else {
        
        echo "no finishing emails found\n";
    }
    

    if( $enableDebug )
    {
        //echo "<pre>", print_r($instagramList), "</pre>";
        
        //echo "<pre>", print_r($finishingList), "</pre>";
    }
