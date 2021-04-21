#!/usr/bin/php
<?php

	function index($index) {
		return system( "/usr/bin/php ~/html/shell/indexer.php -reindex " . $index );
	}

	function startIndex($index = null) {
		# get the start time
		$time = time();
		
		ob_start();

		echo $index . " started" . "\n";

		index($index);

		$minutes = intval( (time() - $time) / 60 );
		$seconds = (time() - $time) % 60;

		echo "Completed in " . $minutes . " minute(s) " . $seconds . " second(s) "  . "\n";
		
		$buffer = ob_get_contents();
		ob_end_clean();
		
		return $buffer;
	}
	
	
	$buffer = startIndex('catalog_product_attribute');
	$buffer .= startIndex('catalog_product_price');
	$buffer .= startIndex('catalog_product_flat');
	
	// send email
    $fromEmail = "it@forevercompanies.com";
    $fromName = "IT";
    
    $mail = new Zend_Mail();        
    $mail->setSubject("TF Price Override Notification: Complete");
    $mail->setBodyText($buffer);
    $mail->setFrom($fromEmail, $fromName);
	
    $mail->addTo("josh.averbeck@diamondnexus.com", "josh.averbeck");
	$mail->addTo("tam.duong@forevercompanies.com", "tam.duong");
	$mail->addTo("ally.yang@forevercompanies.com", "ally.yang");
	$mail->addTo("kelly.daigle@forevercompanies.com", "kelly.daigle");
	$mail->addTo("paul.baum@forevercompanies.com", "paul.baum");
    
    try {
        $mail->send();
    }
    catch(Exception $ex) {
		echo $ex->getMessage();
    }
