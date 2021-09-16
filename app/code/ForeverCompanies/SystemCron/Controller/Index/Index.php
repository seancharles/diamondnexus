<?php

namespace ForeverCompanies\SystemCron\Controller\Index;

use Psr\Log\LoggerInterface;
use Magento\Framework\App\Action\Context;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\User\Model\UserFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection as OrderShipmentCollection;
use Magento\Catalog\Model\ProductFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use ForeverCompanies\Smtp\Helper\Mail as MailHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Sales\Model\OrderFactory;
use Magento\User\Model\ResourceModel\User\CollectionFactory as UserCollectionFactory;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $logger;
	
	protected $orderCollectionFactory;	
	protected $userFactory;
	protected $directory;
	protected $connection;
	protected $storeManager;
	
	protected $productFactory;
	protected $websiteRepository;
	
	protected $mailHelper;
	
	protected $scopeConfig;
	protected $storeScope;
	
	protected $orderFactory;
	protected $userCollectionFactory;
	protected $orderShipmentCollection;

	public function __construct(
		Context $context,
	    LoggerInterface $logger,
	    OrderCollectionFactory $orderCollectionF,
	    UserFactory $userF,
	    Filesystem $fileS,
	    ResourceConnection $resourceC,
	    StoreManagerInterface $storeManagerI,
	    OrderShipmentCollection $orderShipmentC,
	    ProductFactory $productF,
	    OrderFactory $orderF,
	    WebsiteRepositoryInterface $websiteRepositoryI,
	    MailHelper $mailH,
	    ScopeConfigInterface $scopeConfigI,
	    UserCollectionFactory $userCollectionF
	) {
		$this->logger = $logger;
		
		$this->orderCollectionFactory = $orderCollectionF;
		
		$this->userFactory = $userF;
		$this->directory = $fileS->getDirectoryWrite(DirectoryList::VAR_DIR);
		
		$this->connection = $resourceC->getConnection();
		
		$this->storeManager = $storeManagerI;
		
		$this->orderShipmentCollection = $orderShipmentC;
		$this->productFactory = $productF;
		$this->orderFactory = $orderF;
		$this->websiteRepository = $websiteRepositoryI;
		
		$this->mailHelper = $mailH;
		$this->scopeConfig = $scopeConfigI;
		$this->storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$this->userCollectionFactory = $userCollectionF;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    echo 'fff';die;
	    $userList = array();
	    $orderList = array();
	    $itemList = array();
	    
	    $users = $this->userCollectionFactory->create();
	    
	    foreach($users as $user) {
	        $userList[$user->getUserId()] = $user->getUsername();
	    }
	    
	    $shipmentsInPastMonth = $this->orderShipmentCollection
	    ->addAttributeToSelect('created_at')
	    ->addAttributeToSelect('order_id')
	    // ->addAttributeToFilter('shipment_status', array('eq'=>'Shipped'))
	    ->addAttributeToFilter('created_at', array('gt' => date('Y-m-d H:i:s', strtotime('-1 month'))))
	    ->addAttributeToFilter('created_at', array('lt' => date('Y-m-d H:i:s', strtotime('now'))))
	    ->load();
	    
	    foreach ($shipmentsInPastMonth as $recentShipment) {
	        
	        $recentlyShippedOrder = $this->orderFactory->create()->load($recentShipment->getOrderId());
	        
	        switch ($recentlyShippedOrder->getStatus()) {
	            case 'complete':
	            case 'Shipped':
	            case 'delivered':
	                
	                $items = $recentlyShippedOrder->getAllVisibleItems();
	                
	                foreach($items as $item) {
	                    
	                    if( $item->getOriginalPrice() != $item->getPrice() && $item->getOriginalPrice() > 0 ) {
	                        
	                        $temp = $item->toArray();
	                        
	                        $temp['color'] = '#eeeeee';
	                        $temp['increment_id'] = $recentlyShippedOrder->getIncrementId();
	                        $temp['created_at'] = $recentlyShippedOrder->getCreatedAt();
	                        $temp['created_at_ship'] = $recentShipment->getCreatedAt();
	                        
	                        if ($recentlyShippedOrder->getData('sales_person_id') && $recentlyShippedOrder->getData('sales_person_id') != "0") {
	                            $user = $this->userFactory->create()->load($order->getData('sales_person_id'));
	                            $temp['sales_person'] = $user->getUserName();
	                        } else {
	                            $temp['sales_person'] = "Frontend";
	                        }
	                        
	                        $itemList[] = $temp;
	                    }
	                }
	                break;;
	            default:
	                break;;
	        }
	        
	    }
	    
	    ob_start();
	    ?>

        <h1>Summary</h1>
        
        <table border='0' cellpadding='3' cellspacing="1" style="border:solid 1px;">
        
        	<tr>
        		<th bgcolor="lightgray">Shipments Checked</th>
        		<td><?php echo count($shipmentsInPastMonth); ?></td>
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
        		<th bgcolor="lightgray">Ship Date</th>
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
        				<td bgcolor="<?php echo $item['color']; ?>"><?php echo $item['created_at_ship']; ?></td>
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
        	
        	
        	$this->mailHelper->setFrom([
        	    'name' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/custom_price_order_report_tmp/from_name',
        	        $this->storeScope),
        	    'email' => $this->scopeConfig->getValue('forevercompanies_cron_schedules/custom_price_order_report_tmp/from_email',
        	        $this->storeScope)
        	]);
        	
        	$this->mailHelper->addTo(
        	    $this->scopeConfig->getValue('forevercompanies_cron_schedules/custom_price_order_report_tmp/to_email',
        	        $this->storeScope),$this->scopeConfig->getValue('forevercompanies_cron_schedules/custom_price_order_report_tmp/to_name',
        	            $this->storeScope)
        	    );
        	
        	$this->mailHelper->setSubject('Accounting Price Report');
        	$this->mailHelper->setIsHtml(true);
        	$this->mailHelper->setBody($content);
        	$this->mailHelper->send();
        	
        	echo "Complete\n";
	}
}