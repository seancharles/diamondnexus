<?php
namespace ForeverCompanies\CronJobs\Controller\Index;


require_once $_SERVER['HOME'].'magento/shell/dnl/google_api/ProductsFeed.php';

use Psr\Log\LoggerInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use ForeverCompanies\CronJobs\Dnl\Encoding;
use ForeverCompanies\CronJobs\Model\FeedLogic;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $feedModel;
    
	protected $_pageFactory;
	
	protected $logger;
	protected $storeRepository;
	protected $storeManager;
	protected $productCollectionFactory;
	protected $productModel;
	protected $resourceConnection;
	protected $attributeSetMod;
	protected $stockItemModel;
	protected $reviewModel;
	protected $reviewCollection;
	protected $encoder;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
	    LoggerInterface $logger,
	    StoreRepositoryInterface $storeRepositoryInterface,
	    StoreManagerInterface $storeManagerInterface,
	    CollectionFactory $collectionFactory,
	    ProductFactory $productFactory,
	    ResourceConnection $resource,
	    AttributeSetRepositoryInterface $attributeSetRepo,
	    StockItemRepository $stockItemRepo,
	    Review $rev,
	    ReviewCollectionFactory $coll,
	    Encoding $enc,
	    FeedLogic $feed
	)
	{
		$this->_pageFactory = $pageFactory;
		
		$this->logger = $logger;
		
		$this->storeRepository = $storeRepositoryInterface;
		$this->storeManager = $storeManagerInterface;
		$this->productCollectionFactory = $collectionFactory;
		$this->productModel = $productFactory;
		$this->resourceConnection = $resource;
		$this->attributeSetMod = $attributeSetRepo;
		$this->stockItemModel = $stockItemRepo;
		$this->reviewModel = $rev;
		$this->reviewCollection = $coll;
		$this->encoder = $enc;
		$this->feedModel = $feed;
		
		return parent::__construct($context);
	}
	
	public function execute()
	{
	    // you can test here at http://yourstoreurl.com/forever-cron/index or you can wait for the cron jobs to run.
	    // look in var/export.
	    echo 'fff';die;
	    
	    // $this->feedModel->BuildCsvs(1);
	    // $this->feedModel->BuildCsvs(12);
	    // $this->feedModel->createReviews(1);
	    // $this->feedModel->updateReviews(1);
	    
	    
	   // $this->feedModel->updateReviews(1);
	    
	//    $this->feedModel->createReviews(1);
	    
	    return;
	    $argv = array();
	    
	    $argvStoreId = 1;
	    
	    $argv[1] = "CSV";
	    $GLOBALS['argvStoreId'] = 1;
	    
	    switch ($argv[1]) {
	        case 'status':
	            $this->doCheckStatus();
	            break;
	        case 'single':
	            if(!isset($argv[3])) {
	                throw new Exception('Product ID must be defined');
	            }
	            $listing = $this->getOneProduct($argv[3]);
	            $this->doFeed($listing);
	            break;
	        case 'checksingle':
	            if(!isset($argv[3])) {
	                throw new Exception('Product ID must be defined');
	            }
	            $listing = $this->getOneProduct($argv[3]);
	            print_r($listing);
	            #doCheckFeed($listing);
	            break;
	        case 'all':
	            $listing = $this->getAllProductList();
	            $this->createCSV($listing);
	            $this->createYahooCSV($listing);
	            $this->createFBCSV($listing);
	            $this->doFeed($listing);
	            $this->cleanFeed();
	            break;
	        case 'API':
	            $listing = $this->getAllProductList();
	            $this->doFeed($listing);
	            $this->cleanFeed();
	            break;
	        case 'check':
	            $listing = $this->getAllProductList();
	            $this->doCheckFeed($listing);
	            break;
	        case 'clean':
	            $this->cleanFeed();
	            break;
	        case 'update':
	            $listing = $this->getRecentProductList();
	            $this->doFeed($listing);
	            $this->cleanFeed();
	            break;
	        case 'createReviews':
	            $this->getProductsReviews('full');
	            break;
	        case 'updateReviews':
	            $this->getProductsReviews('inc');
	            break;
	        case 'accountInfo':
	            $this->getAccountInfo();
	            break;
	        case 'CSV':
	            $listing = $this->getAllProductList();
	            $this->createCSV($listing);
	            $this->createYahooCSV($listing);
	            $this->createFBCSV($listing);
	            break;
	        case 'FB':
	            $listing = $this->getAllProductList();
	            $this->createFBCSV($listing);
	            break;
	        default:
	            $this->usage();
	            break;
	    }
	    
	    
	    
	    die;
	    
	    
	    
	}
	
	
}