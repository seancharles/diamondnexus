<?php

 //   require_once $_SERVER['HOME'].'magento//Mage.php';
    Mage::app();

	/**
	 * Get the resource model
	 */
	$resource = Mage::getSingleton('core/resource');
	
	/**
	 * Retrieve the read connection
	 */
	$readConnection = $resource->getConnection('core_read');
    
    // custom date range
    if ( isset($argv)  && count($argv) == 3 ) {
    
        // example: php dnDailyOrderCSR.php yesterday now
        // example: php dnDailyOrderCSR.php "jan 1st" "last friday"
        $date = date('Y-m-d', strtotime($argv[1]));
        $tosdate = date('Y-m-d', strtotime($argv[2]));
        
        print_r($argv);
    
    } else {
        
        $date = date('Y-m-d', strtotime('now -30 day'));  // now -30 day
        $tosdate = date('Y-m-d', strtotime('now'));  // now -1 day
    }
    
    $fromDate = $date.' 00:00:00';
    $toDate = $tosdate.' 23:59:59';

    $filename = $_SERVER['HOME'].'/html/var/report/csr-' . $date . '.csv';

    $orderQuery = "SELECT
                        so.increment_id,
                        CONCAT(a.firstname,' ', a.lastname) sales_rep,
                        so.status,
                        CONCAT(so.customer_firstname,' ',so.customer_lastname) customer_name,
                        so.customer_email,
                        ba.telephone billing_phone,
                        so.created_at,
                        so.grand_total
                        # i.sku,
                        # i.name,
                        # i.row_total
                    FROM
                        sales_flat_order so
                    LEFT JOIN
                        admin_user a ON so.sales_person_id = a.user_id
                    LEFT JOIN
                        sales_flat_order_address ba ON so.billing_address_id = ba.entity_id
                    # LEFT JOIN
                    #     sales_flat_order_item i ON so.entity_id = i.order_id
                    WHERE
                        so.created_at BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
                    AND
                        so.status NOT IN('canceled')
                    # AND
                    #     i.sku NOT IN('ADDDISC','NOTES')
                    AND
                        so.store_id = 5
                    ORDER BY
                        increment_id DESC";
    
    echo $orderQuery . "\n";
    
    $orderResult = $readConnection->fetchAll($orderQuery);

    echo count($orderResult) . " rows found!\n";
    
    $report[0] = array(
        "Order Id",
        "Sales Rep",
        "Status",
        "Name",
        "Email",
        "Phone",
        "Date",
        "Grand Total"
        # "SKU",
        # "Item",
        # "Item Total"
    );
    
    foreach($orderResult as $order) {
        
        $report[] = array(
            $order['increment_id'],
            $order['sales_rep'],
            $order['status'],
            $order['customer_name'],
            $order['customer_email'],
            $order['billing_phone'],
            $order['created_at'],
            $order['grand_total'],
            # $order['sku'],
            # $order['name'],
            # $order['row_total']
        );
    }
    
    $csv=new Varien_File_Csv();
    $csv->saveData($filename,$report);

    $mail = new Zend_Mail();
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
    $attachment = new Zend_Mime_Part($content);
    $attachment->type = mime_content_type($filename);
    $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
    $attachment->encoding = Zend_Mime::ENCODING_BASE64;
    $attachment->filename = 'cr-' . $date . '.csv';

    if( count($orderResult) > 0 ) {
        $mail->addAttachment($attachment);
    }
    
    $mail->send();
    
    echo "Email sent!\n";
?>
