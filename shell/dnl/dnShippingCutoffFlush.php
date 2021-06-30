#!/usr/bin/php
<?php

// require_once $_SERVER['HOME'].'magento//Mage.php';
Mage::app();

function flushCacheKey($redis, $key = null) {
	$cacheKeyList = $redis->smembers($key);
	// loop through returned keys and delete
	foreach($cacheKeyList as $key) {
		// build the cache key
		$cacheKey = 'zc:k:' . $key;
		// debug cache key value
		//echo "<pre>", print_r($redis->hgetall($cacheKey)), "</pre>";
		$redis->del($cacheKey);
	}
}

$logFile = '2pm-cache-flush.log';

// available by cutoff dates
$holidayCutoffDates = [
	'2020-12-04',
	'2020-12-10',
	'2020-12-15',
	'2020-12-16',
	'2020-12-17',
	'2020-12-21',
	'2020-12-22',
	'2020-12-23',
	'2020-12-28'
];

$date = date("Y-m-d");

Mage::log('Initiated 2pm flush routine', null, $logFile, true);

// conditionally also run the available by update when needed
if(in_array($date, $holidayCutoffDates) == true) {
	
	Mage::log('Available by update started', null, $logFile, true);
	
	echo system('/usr/bin/php /home/admin/shell/dnl/dnFilterAvailablebyUpdate.php');
	
	Mage::log('Available by update completed', null, $logFile, true);
	
	echo system('/usr/bin/php /home/admin/shell/indexer.php -reindex catalog_product_attribute');
	
	Mage::log('Reindex completed', null, $logFile, true);
}

// REDIS cache flushing
Mage::log('Begin Redis cache flush', null, $logFile, true);

$redis = new Credis_Client('liveawsredis.d9zr26.ng.0001.use2.cache.amazonaws.com', 6379);
$redis->select(4);

// clear products and categories cache
foreach ($redis->smembers('zc:tags') as $item) {
	if (preg_match('/^c[0-9]*/', $item) || preg_match('/^p[0-9]*/', $item)) {
	 	foreach ($redis->smembers('zc:ti:'.$item) as $category) {
	 		$redis->del('zc:k:'.$category);
	 	}
	}
}

Mage::app()->getCacheInstance()->cleanType($type);
Mage::dispatchEvent('adminhtml_cache_refresh_type', array('type' => $type));

$redis->close();

Mage::log('Redis Cache Flush Completed', null, $logFile, true);

// LLNW cache flushing

// conditionally run the product category flush
if(in_array($date, $holidayCutoffDates) == true) {
	echo system('/usr/bin/php /home/admin/limelight/dnFlushProductsCategories.php');
} else {
	echo system('/usr/bin/php /home/admin/limelight/dnFlushProducts.php');
}

Mage::log('LLNW Category Cache Flush Completed', null, $logFile, true);
