<?php
    # Pull orders that are at least 7 days old
    # Order must be in shipped status (diamond nexus orders only)
    # Send post purchase email to customer

    # Create new table that will track post purchase emails
    # If someone has recieved an email for post purchase or it is failing

    # We will run the cron once an hour and create a check alert to report any emails that didn't get sent out
    # Emails that fail will not be sent out infinitely later. After the fourth day emails will no longer attempt to send

//    require_once $_SERVER['HOME'] . 'magento//Mage.php';
    
    Mage::app();
    
    CONST REASON_KEY = 'dnWarrantySurvey001';

    $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');

	$emailTransactionModel = Mage::getSingleton('postpurchase/log');

    $query = "SELECT
                o.entity_id AS order_entity_id,
                o.increment_id AS order_increment_id,
                o.customer_email AS order_customer_email,
                o.customer_firstname AS order_customer_firstname,
                o.customer_lastname AS order_customer_lastname,
                o.created_at AS order_created_at,
                o.store_id AS order_store_id,
                s.entity_id AS shipment_entity_id,
                s.created_at AS shipment_created_at,
                s.entity_id AS shipment_entity_id,
                DATEDIFF(now(), s.created_at) AS days_from_shipment,
                IFNULL(l.sent, 0) AS sent,
                IFNULL(l.failed, 0) AS failed,
                l.email_id AS email_id,
                l.reason_key
            FROM
                sales_flat_order o
            INNER JOIN
                sales_flat_shipment s ON o.entity_id = s.order_id
            LEFT JOIN
                diamondnexus_email_log l ON o.entity_id = l.order_id
            WHERE
                o.store_id IN(8, 18)
            AND
                datediff(now(), s.created_at) between 7 and 14";

	echo $query . "\n";

    $orderList = $readConnection->fetchAll($query);

	foreach($orderList as $order) {

		// we reset error to false for every item
		$error = false;

		// Transactional Email Template's ID
		$templateId = 66;

		// Set sender information
		$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
		$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');

		$sender = array(
			'name' => $senderName,
			'email' => $senderEmail
		);

		// Set variables that can be used in email template
		$vars = array();

		$translate  = Mage::getSingleton('core/translate');
        
        // make sure the email hasn't already been sent
        if( $order['sent'] != 1 ) {
            
            // only send emails for warranty survey purposes
            if( $order['reason_key'] == null || $order['reason_key'] == REASON_KEY ) {
             
                echo "Sending email to " . $order['order_customer_email'] . "...\n";
             
                print_r($order);
                
                try {
                    // Send Transactional Email
                    Mage::getModel('core/email_template')->sendTransactional(
                        $templateId,
                        $sender,
                        $order['order_customer_email'],
                        $order['order_customer_firstname'] . " " . $order['order_customer_firstname'],
                        $vars,
                        $order['order_store_id']
                    );

                    $translate->setTranslateInline(true);
                }
                catch (Exception $e) {

                    print($e->getMessage());

                    // this will log and increment the failed attempts number
                    $error = true;

                } finally {
                
                    $fields = array(
                        'order_id' => $order['order_entity_id'],
                        'created_at' => date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time())),
                        'reason_key' => REASON_KEY
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
                    
                    $emailTransactionModel->setData($fields);
                    $emailTransactionModel->save();
                }
             
            }
            
        }
	}

