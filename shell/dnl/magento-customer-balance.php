<?php
// require_once $_SERVER['HOME'].'magento//Mage.php'; umask(0); Mage::app('default');

setlocale(LC_MONETARY, 'en_US');
$filename = $_SERVER['HOME']."magento//var/report/customer-balances.csv";

unlink($filename);
$count = 0;

$resource = Mage::getSingleton('core/resource');
$readConnection = $resource->getConnection('core_read');

$balances = $readConnection->fetchAll("select email,customer_points_usable,amount from rewards_customer_index_points, customer_entity inner join enterprise_customerbalance ON enterprise_customerbalance.customer_id = customer_entity.entity_id where rewards_customer_index_points.customer_id = customer_entity.entity_id and (customer_points_usable > 0 or enterprise_customerbalance.amount > 0)");

$line = implode(',', array(
	"Customer",
	"ISC Balance",
	"Points Balance",
)). "\n";
file_put_contents($filename, $line, FILE_APPEND);
foreach ($balances as $balance) {
	if ($balance['amount'] > 0 || $balance['customer_points_usable'] > 0) {
		$line = implode(',', array(
			$balance['email'],
			$balance['amount'],
			$balance['customer_points_usable'],
		)). "\n";
		file_put_contents($filename, $line, FILE_APPEND);
	}
}
?>
