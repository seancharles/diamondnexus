<?php
    require_once $_SERVER['HOME'] . '/html/app/Mage.php';

    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
	echo Mage::helper('salesforces')->syncCustomers();