<?php
//	require_once $_SERVER['HOME'].'magento//Mage.php';
	
	Mage::app();
    
    $type = 'collections';
    
    // clear collections cache
    Mage::app()->getCacheInstance()->cleanType($type);
    Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));
