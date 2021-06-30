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
    
    function generateLeadsCSV($filename = null, $year = 0, $minWidth = 0, $maxWidth = 0) {
        
        global $readConnection;
        
        # generate output file
        $file = $_SERVER['HOME'] . '/html/var/report/' . $filename;
        
        $leadsQuery = "SELECT
                            vs.email_address,
                            vs.website_id,
                            vs.visitor_page_id,
                            vs.slider_id,
                            vs.listrak_sub_id,
                            vs.slider_source,
                            vs.subject,
                            
                            vs.user_agent,
                            vs.screen_width,
                            vs.screen_height,
                            
                            vs.adwords_campaign,
                            vs.utm_cookie_keyword,
                            vs.utm_cookie_campaign,
                            vs.utm_cookie_medium,
                            vs.utm_cookie_last_visit,
                            vs.utm_cookie_source,
                            
                            vs.submitted_at
                        FROM (
                            SELECT
                                MIN(visitor_submission_id) as visitor_submission_id,
                                LOWER(email_address) as email_address
                            FROM
                                visitor_submissions
                            WHERE
                                website_id = 1
                            AND
                                email_address <> ''
                            GROUP BY
                                email_address
                            ) as tmp
                        LEFT JOIN
                            visitor_submissions vs ON tmp.visitor_submission_id = vs.visitor_submission_id
                        WHERE
                            submitted_at BETWEEN '" . $year . "-01-01 00:00:00' AND '" . ($year+1) . "-01-01 00:00:00'
                        AND
                            screen_width " . ( ($minWidth == null && $maxWidth == null) ? "IS NULL" : "BETWEEN " . $minWidth . " AND " . $maxWidth ) . "
                        ORDER BY
                            submitted_at ASC;";
        
        echo "Running query\n";
        
        echo $leadsQuery . "\n";
        
        $leadsResult = $readConnection->fetchAll($leadsQuery);
        
        $report[0] = array(
            "Email",
            "website_id",
            "visitor_page_id",
            "slider_id",
            "listrak_sub_id",
            "slider_source",
            "subject",
            
            "user_agent",
            "screen_width",
            "screen_height",
            
            "adwords_campaign",
            "utm_cookie_keyword",
            "utm_cookie_campaign",
            "utm_cookie_medium",
            "utm_cookie_last_visit",
            "utm_cookie_source",
            "Date"
        );
        
        foreach($leadsResult as $lead) {
            
            $report[] = array(
                $lead['email_address'],
                $lead['website_id'],
                $lead['visitor_page_id'],
                $lead['slider_id'],
                $lead['listrak_sub_id'],
                $lead['slider_source'],
                $lead['subject'],
                
                $lead['user_agent'],
                $lead['screen_width'],
                $lead['screen_height'],
                
                $lead['adwords_campaign'],
                $lead['utm_cookie_keyword'],
                $lead['utm_cookie_campaign'],
                $lead['utm_cookie_medium'],
                $lead['utm_cookie_last_visit'],
                $lead['utm_cookie_source'],
                $lead['submitted_at']
            );
            
            echo (count($report) / count($leadsResult)) * 100 . "%\n";
            
        }
        
        $csv=new Varien_File_Csv();
        $csv->saveData($file,$report);
        
        return $filename;
    }

    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-desktop-2016.csv', 2016, 1025, 9999) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-tablet-2016.csv', 2016, 769, 1024) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-mobile-2016.csv', 2016, 0, 768) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-unspecified-2016.csv', 2016) . "\n";
    
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-desktop-2017.csv', 2017, 1025, 9999) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-tablet-2017.csv', 2017, 769, 1024) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-mobile-2017.csv', 2017, 0, 768) . "\n";
    echo "http://paul.diamondnexus.com/var/report/" . generateLeadsCSV('new-leads-unspecified-2017.csv', 2017) . "\n";
    
