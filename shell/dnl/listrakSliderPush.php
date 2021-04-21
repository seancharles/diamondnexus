<?php
	require_once '/home/admin/html/app/Mage.php';
	Mage::app();
    
    // Begin Listrak insert code
    $listrak = new Listrak_Mail();
    
    # get the current locale adjust timestamp
    $time = Mage::getModel('core/date')->timestamp(time());
    
    # filter submissions for the last 24 hours
    $filterTime = date('Y-m-d H:i:s', $time - 86400 );
    
    # pull the submissions that have not been posted to listrak
    $submissionCollection = Mage::getModel('visitor/submission')->getCollection()
        ->setOrder('visitor_submission_id', 'DESC')
        ->addFieldToFilter('submitted_at', array('gt' => $filterTime))
        ->addFieldToFilter('listrak_data_pushed', 2);
    
    # if we found a submission we will update the record
    if( $submissionCollection->count() > 0 ) {
        
        foreach( $submissionCollection as $submission ) {
            
            echo "Pushing submission: " . $submission->getVisitorSubmissionId() . " - " . $submission->getEmailAddress() . "\n";
            
            // Assign the attributes
            $attributes = array(
                array(2387158, $submission->getGender()),
                array(1622236, $submission->getFirstName()),
                array(1622238, $submission->getLastName()),
                
                array(2385199, (($submission->getSendEngagementCatalog()) ? 'on' : 'off' )),
                array(2385200, (($submission->getEngagementRing() == 'Yes') ? 'on' : 'off' )),
                array(2385201, $submission->getTypeNeed()),
                //array(2385203, implode(" ", $submission->getItemsOfInterest())),
                array(2385202, $submission->getNeedBy()),

                array(2391959, $submission->getAddress_1()),
                array(2397937, $submission->getAddress_2()),
                array(2391314, $submission->getCity()),
                array(1622239, $submission->getRegion()),
                array(1622237, $submission->getPostalCode()),
                
                array(2387241, $submission->getHttpReferer()),
                array(2387236, $submission->getListrakSubId()),
                array(2393212, $submission->getSliderSource()),
                array(2387237, $submission->getUtmCookieSource()),
                array(2387238, $submission->getUtmCookieMedium()),
                array(2387239, $submission->getUtmCookieCampaign()),
                array(2387240, $submission->getUtmCookieTerm())
            );
            
            if( $submission->getWebsiteId() == 3 ) {
                $attributes[] = array(
                    2409144, "on"
                );
            }

            $listrak->setContact( $submission->getEmailAddress(), $submission->getListrakEventId(), false, $attributes );
            
            // mark the submission as pushed
            $submission->setListrakDataPushed(1);
            $submission->save();
            
            // TODO add in error handling for when API fails to mark emails as not sent
            
            //print_r($submission);
            //print_r($attributes);
        }
        
    } else {
        echo "No submissions found. closing\n";
    }
