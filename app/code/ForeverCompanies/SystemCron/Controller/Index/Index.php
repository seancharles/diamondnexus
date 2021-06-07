<?php

namespace ForeverCompanies\SystemCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $logger;
	
	protected $orderCollectionFactory;
	protected $userFactory;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    UserFactory $userF
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		$this->userFactory = $userF;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    $orders = $this->orderCollectionFactory->create();
	    
	    $orders->addAttributeToFilter('created_at', array('gt' => date('Y-m-d H:i:s', strtotime('yesterday'))));
	    $orders->addAttributeToFilter('created_at', array('lt' => date('Y-m-d H:i:s', strtotime('now'))));
	    
	    $orders->load();
	    
	    $itemList = array();
	    
	    foreach($orders as &$order) {
	        
	        $items = $order->getAllVisibleItems();
	        
	        foreach($items as $item) {
	            
	            if( $item->getOriginalPrice() != $item->getPrice() && $item->getOriginalPrice() > 0 ) {
	                
	                $temp = $item->toArray();
	                
	                $temp['color'] = '#eeeeee';
	                $temp['increment_id'] = $order->getIncrementId();
	                $temp['created_at'] = $order->getCreatedAt();
	                
	                if ($order->getData('sales_person_id') && $order->getData('sales_person_id') != "0") {
	                    
	                    $user = $this->userFactory->create()->load($order->getData('sales_person_id'));
	                    $temp['sales_person'] = $user->getUserName();
	                } else {
	                    $temp['sales_person'] = "Frontend";
	                }
	                
	                
	                $itemList[] = $temp;
	            }
	        }
	    }
	    
	    ob_start();
	    ?>

<h1>Summary</h1>

<table border='0' cellpadding='3' cellspacing="1" style="border:solid 1px;">

	<tr>
		<th bgcolor="lightgray">Orders Checked</th>
		<td><?php echo count($orders); ?></td>
	</tr>
	<tr>
		<th bgcolor="lightgray">Orders With Custom Price</th>
		<td><?php echo count($itemList); ?></td>
	</tr>
</table>

<h1>Details</h1>

<table border='0' cellpadding='3' cellspacing="1" style="border:solid 1px;">
	<tr>
		<th bgcolor="lightgray">Order</th>
		<th bgcolor="lightgray">Order Date</th>
		<th bgcolor="lightgray">Sales Person</th>
		<th bgcolor="lightgray">Item</th>
		<th bgcolor="lightgray">List Price</th>
		<th bgcolor="lightgray">Sold For Price</th>
		<th bgcolor="lightgray">Amount Discounted</th>
		<th bgcolor="lightgray">Percent Discounted</th>
	</tr>
	<?php if( count($itemList) > 0 ) { ?>
		<?php foreach($itemList as $item) { ?>
			<tr>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo $item['increment_id']; ?></td>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo $item['created_at']; ?></td>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo $item['sales_person']; ?></td>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo $item['name']; ?></td>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo number_format($item['original_price'], 2); ?></td>
				<td bgcolor="<?php echo $item['color']; ?>"><?php echo number_format($item['price'], 2); ?></td>
				<td bgcolor="<?php echo $item['color']; ?>">$<?php echo number_format($item['original_price'] - $item['price'], 2); ?></td>
				<?php if ($item['original_price'] > 0): ?>
					<td bgcolor="<?php echo $item['color']; ?>"><?php echo number_format(((1 - ($item['price'] / $item['original_price'])) * 100 ), 2); ?>%</td>
				<?php endif; ?>
			</tr>
		<?php } ?>
	<?php } else { ?>
		<tr>
			<td colspan="6">No problems found</td>
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
	
	echo '<pre>';
	var_dump("content", $content);
	
	# sends to email forwarder group
    mail('CustomPriceOrderReportList@diamondnexus.com','Custom Price Report',$content,'Content-type: text/html');
	
	echo "Complete\n";

	}
}