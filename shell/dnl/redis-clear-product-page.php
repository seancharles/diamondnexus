<?php
	ini_set('display_errors', '1');
//	require_once $_SERVER['HOME'].'magento//Mage.php';
	Mage::app();

	// Get the current store id
	$storeId = Mage::app()->getStore()->getId();
	$userModel = Mage::getModel('admin/user');
	$userModel->setUserId(0);
	Mage::getSingleton('admin/session')->setUser($userModel);

	$host = $argv[1];
	$port = $argv[2];
	$database = $argv[3];
   
	$redis = new Credis_Client($host, $port);
   
	$redis->select($database);
	
	foreach ($redis->smembers('zc:tags') as $item) {
		if (preg_match('/^p[0-9]*/', $item)) {
			foreach ($redis->smembers('zc:ti:'.$item) as $page) {
				$redis->del('zc:k:'.$page);
			}
		}
	}
	$redis->close();
  
