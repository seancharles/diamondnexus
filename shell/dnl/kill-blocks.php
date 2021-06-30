<?
$result = system("ps -ef | grep magento-build-product-blocks.php | grep -v grep | awk '{print $2}'");
if(is_numeric($result)) {
	print "Block Builder running, killing it.\n";
	system("kill -9 ".$result);
}
system("rm -f /tmp/product-blocks.lck");
?>
