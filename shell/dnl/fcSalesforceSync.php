<?php

 //   require_once $_SERVER['HOME'] . 'magento//Mage.php';
    
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

	$auth = Mage::getModel('salesforce/connector')->getAuth();
	
	Mage::register('salesforce_connection', $auth, false);
	
	$sync = Mage::getModel('salesforces/observer');
	$sync->updateSalesforce();

