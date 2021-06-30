<?php

//    require_once $_SERVER['HOME'].'magento//Mage.php';
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
    
        // example: php dnDailyCatalogRequests.php yesterday now
        // example: php dnDailyCatalogRequests.php "jan 1st" "last friday"
        $date = date('Y-m-d', strtotime($argv[1]));
        $tosdate = date('Y-m-d', strtotime($argv[2]));
        
        print_r($argv);
    
    } else {
        
        $date = date('Y-m-d', strtotime('now -1 day'));  // now -1 day
        $tosdate = date('Y-m-d', strtotime('now'));  // now -1 day
    }
    
    $fromDate = $date.' 06:00:00';
    $toDate = $tosdate.' 06:00:00';

    $filename = $_SERVER['HOME'].'/html/var/report/cr-' . $date . '.csv';

    $catalogQuery = "SELECT
        *
    FROM
        visitor_submissions
    WHERE
        submitted_at BETWEEN '" . $fromDate . "' AND '" . $toDate . "'
    AND
        send_engagement_catalog = 1
    ORDER BY
        submitted_at ASC;";
    
    echo $catalogQuery . "\n";
    
    $leadsResult = $readConnection->fetchAll($catalogQuery);

    echo count($leadsResult) . " rows found!\n";
    
    $report[0] = array(
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
    );
    
    foreach($leadsResult as $lead) {
        
        $report[] = array(
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
        );
    }
    
    if( count($leadsResult) > 0 ) {
        
    } else {
        
    }
    
    $csv=new Varien_File_Csv();
    $csv->saveData($filename,$report);

    $mail = new Zend_Mail();
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
        ->setSubject('Daily Catalog Request Report - ' . $date . ( (count($leadsResult) == 0) ? ': No Leads Submitted' : '' ));

    $content = file_get_contents($filename);
    $attachment = new Zend_Mime_Part($content);
    $attachment->type = mime_content_type($filename);
    $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
    $attachment->encoding = Zend_Mime::ENCODING_BASE64;
    $attachment->filename = 'cr-' . $date . '.csv';

    if( count($leadsResult) > 0 ) {
        $mail->addAttachment($attachment);
    }
    
    $mail->send();
    
    echo "Email sent!\n";
?>
