<?php

namespace ForeverCompanies\StoneEmail\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Sales\Model\OrderFactory;

class StoneEmail
{    
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;
    protected $mediaTmpDir;
    protected $file;
    protected $connection;
    
    protected $booleanMap;
    protected $csvHeaderMap;
    protected $clarityMap;
    protected $cutGradeMap;
    protected $colorMap;
    protected $shapeMap;
    protected $supplierMap;
    
    protected $shapePopMap;
    protected $shapeAlphaMap;
    protected $shippingStatusMap;
    
    protected $claritySortMap;
    protected $cutGradeSortMap;
    protected $colorSortMap;
    
    protected $csv;
    
    protected $fileName;
    protected $requiredFieldsArr;
    
    protected $orderFactory;
    
    
    public function __construct(
        CollectionFactory $collectionFactory,
        Product $prod,
        ProductFactory $prodF,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo,
        Csv $cs,
        DirectoryList $directoryList,
        File $fil,
        OrderFactory $orderF
    ) {
        $this->productCollectionFactory = $collectionFactory;
        $this->productModel = $prod;
        $this->resourceConnection = $resource;
        $this->attributeSetMod = $attributeSetRepo;
        $this->stockItemModel = $stockItemRepo;
        $this->csv = $cs;
        $this->productFactory = $prodF;
        $this->file = $fil;
        
        $this->mediaTmpDir = $directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp';
        $this->file->checkAndCreateFolder($this->mediaTmpDir );
        $this->connection = $resource->getConnection();
        
        $this->orderFactory = $orderF;
    }
    
    function run()
    {   
        
        $order = $this->orderFactory->create()->load(553555);
        
        echo 'ok the order increment id is ' . $order->getIncrementId() . '<br />';
        
        foreach ($order->getAllItems() as $orderItem) {
            
            if ($orderItem->getProduct()->getTypeId() == "configurable") {
                continue;
            }
            
            if (1==1 || $orderItem->getProduct()->getProductType() == "3569") { // if is diamond
                echo 'the order item product type is ' . $orderItem->getProduct()->getProductType() . '<br />';
                echo 'the order item product supplier is ' . $orderItem->getProduct()->getSupplier() . '<br />';
                
                echo 'the order item type id is ' . $orderItem->getProduct()->getTypeId() . '<br />';
                
                echo 'the order item attribute set id ' . $orderItem->getProduct()->getAttributeSetId() . '<br />';
                
            }
           
            
            
        }
        
        die;
    }
}