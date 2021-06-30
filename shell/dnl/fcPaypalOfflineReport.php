<?php
//	require_once $_SERVER['HOME'].'magento//Mage.php';
	
	Mage::app();

	date_default_timezone_set('America/Chicago');
	
	$orders = Mage::getModel('sales/order')->getCollection();
	
	$begin = date('Y-m-d', strtotime('yesterday')) . " 00:00:00";
	$end = date('Y-m-d', strtotime('yesterday')) . " 23:59:59";

	$orders->addAttributeToFilter('created_at', array('gt' => $begin));
	$orders->addAttributeToFilter('created_at', array('lt' => $end));
	
	$orderList = [];
	
	foreach($orders as $order) {
		if($order->getPayment()->getMethod() == 'multipay') {
			
			$transaction_list = Mage::getModel('diamondnexus_multipay/transaction')
				->getCollection()
				->addFieldToFilter('order_id', array('eq' => $order->getId()))
				->load();

			$transactions = [];
				
			if( $transaction_list )
			{
				foreach( $transaction_list as $transaction )
				{
					if($transaction['method'] == DiamondNexus_Multipay_Model_Constant::MULTIPAY_PAYPAL_OFFLINE_METHOD)
					{
						$transactions[] = "Paypal Offline";
						
					} elseif($transaction['method'] == DiamondNexus_Multipay_Model_Constant::MULTIPAY_CASH_METHOD){
						
						$transactions[] = "Cash";
					}
				}
				
				if(count($transactions) > 0) {
					$orderList[] = [
						'increment_id' => $order->getIncrementId(),
						'customer_name' => $order->getCustomerName(),
						'method' => implode(", ", $transactions),
						'total_paid' => $order->getTotalPaid(),
						'grand_total' => $order->getGrandTotal(),
						'created_at' => $order->getCreatedAt()
					];
				}
			}
		}
	}
	
	ob_start();
?>

<h1>Order Details</h1>

<table border='0' cellpadding='3' cellspacing="1" style="border:solid 1px;">
	<tr>
		<th bgcolor="lightgray">Order</th>
		<th bgcolor="lightgray">Customer Name</th>
		<th bgcolor="lightgray">Payment Methods</th>
		<th bgcolor="lightgray">Amount Paid</th>
		<th bgcolor="lightgray">Grand Total</th>
		<th bgcolor="lightgray">Date</th>
	</tr>
	<?php if( count($orderList) > 0 ) { ?>
		<?php foreach($orderList as $item) { ?>
			<tr>
				<td><?php echo $item['increment_id']; ?></td>
				<td><?php echo $item['customer_name']; ?></td>
				<td><?php echo $item['method']; ?></td>
				<td><?php echo $item['total_paid']; ?></td>
				<td><?php echo $item['grand_total']; ?></td>
				<td><?php echo $item['created_at']; ?></td>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<tr>
			<td colspan="6">Orders Found</td>
		</tr>
	<?php } ?>
</table>

<br />
<br />

<span style="font-size:10px;">
    <?php echo "Sent From <strong>@mag4:{$_SERVER['PWD']}/{$_SERVER['SCRIPT_FILENAME']}</strong>\n"; ?>
</span>

<?php
	$content = ob_get_clean();
	
	$mail = new Zend_Mail();
	$mail->setBodyHtml($content);
	$mail->setFrom('it@forevercompanies.com', 'Forever Companies Reports');
	$mail->addTo('accounting@forevercompanies.com', 'Accounting');
	$mail->addTo('paul.baum@forevercompanies.com', 'Paul Baum');
	$mail->setSubject('Paypal Offline Orders Report');
	$mail->send(); 
	
	echo "Complete\n";
?>
