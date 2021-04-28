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
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as AttributeOptionCollection;

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
    protected $stockRegistry;
    protected $attributeModel;
    protected $attributeOptionCollectionModel;
    protected $date;
    
    
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
        OrderFactory $orderF,
        StockRegistryInterface $stockReg,
        Attribute $attribute,
        AttributeOptionCollection $attributeOptionCollection,
        DateTime $dateTime
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
        
        $this->stockRegistry = $stockReg;
        $this->attributeModel = $attribute;
        $this->attributeOptionCollectionModel = $attributeOptionCollection; 
        $this->date = $dateTime;
    }
    
    function run()
    {   
        // need to check for multiple orders and handle.
        $order = $this->orderFactory->create()->load(553555);
        
        foreach ($order->getAllItems() as $orderItem) {
            
            if ($orderItem->getProduct()->getTypeId() == "configurable") {
                continue;
            }
            
            if (1==1 || $orderItem->getProduct()->getProductType() == "3569") { // if is diamond
                
                $storeName = $order->getStore()->getGroup()->getName();
                $product = $this->productFactory->create()->load($orderItem->getProduct()->getId());
                $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $product->save();
                
                $stockItem = $this->stockRegistry->getStockItemBySku($orderItem->getProduct()->getSku());
                $stockItem->setQty(0);
                $stockItem->setIsInStock(false); // this line
                $this->stockRegistry->updateStockItemBySku($orderItem->getProduct()->getSku(), $stockItem);
                
                
                $supplier = $this->_getSupplierName($product->getSupplier());
                $query = 'SELECT `email` FROM `stones_supplier` WHERE `name` = "' . $supplier . '"';
                
                $supplierEmail = $this->connection->fetchAll($query)[0]['email'];
                
                $html = "Hello,<br/><br/>";
                $html .= "This email confirms sale of your diamond, Stock # " . $product->getSku() . ", sold through "
                    . $storeName . ". Please place this diamond on hold. You will shortly receive a Purchase Order from our fulfillment team at "
                    . $storeName . " with further instructions.<br /><br />";
               
                    
                $html .= "Order Date: " . $this->date->gmtdate('F j Y h:i:s') . " GMT<br /><br />";
                $html .= "SKU     " . $product->getSku() . '<br />';
                $html .= "Weight  " . $product->getWeight() . '<br />';
                $html .= "Shape   " . $product->getResource()->getAttribute('shape')->getFrontend()->getValue($product) . '<br />';
                $html .= "Clarity " . $product->getResource()->getAttribute('clarity')->getFrontend()->getValue($product) . '<br />';
                $html .= "Color   " . $product->getResource()->getAttribute('color')->getFrontend()->getValue($product) . '<br />';
                $html .= "Cut     " . $product->getResource()->getAttribute('cut_grade')->getFrontend()->getValue($product) . '<br />';
                $html .= 'Cert URL <a href="' . $product->getCertUrlKey() . '">' . $product->getCertUrlKey() . '</a><br /><br />';
                $html .= "Thank You!<br /><br />";
                $html .= $storeName . " Fulfillment Team<br /><br />";
                $html .= '<a href="mailto:loosestones@forevercompanies.com">loosestones@forevercompanies.com</a><br /><br />';
                $html .= $storeName . " is a subsidiary of Lautrec Corporation, doing business as Forever Companies.";
                
                
                echo $html;die;
                
                $i=1;
                if (strpos($supplierEmail, ',') !== false) {
                    
                }
                else {
                    
                }
                    
                // $supplierEmail .= ",fulfillment@forevercompanies.com";
                $subject = "Notice of Diamond Sale. Stock # " . $product->getSku();
                
                // To send HTML mail, the Content-type header must be set
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'Content-type: text/html; charset=utf-8';
                
                // Additional headers
                $headers[] = 'To: Mary <mary@example.com>, Kelly <kelly@example.com>';
                $headers[] = 'From: Birthday Reminder <birthday@example.com>';
                $headers[] = 'Cc: birthdayarchive@example.com';
                $headers[] = 'Bcc: birthdaycheck@example.com';
                
                // Mail it
                mail($supplierEmail, $subject, $message, implode("\r\n", $headers));
                
                echo '<pre>';
                var_dump("result", $supplierEmail);
                
                die;
                
                
                echo 'the order item product type is ' . $orderItem->getProduct()->getProductType() . '<br />';
                echo 'the order item product supplier is ' . $orderItem->getProduct()->getSupplier() . '<br />';
                
                echo 'the order item type id is ' . $orderItem->getProduct()->getTypeId() . '<br />';
                
                echo 'the order item attribute set id ' . $orderItem->getProduct()->getAttributeSetId() . '<br />';
                
            }
           
            
            
        }
        
        die;
    }

    
    protected function _getSupplierName($supplierOptionId)
    {
        
        $attributeOptionAll = $this->attributeOptionCollectionModel
        ->setPositionOrder('asc')
        ->setAttributeFilter(
            $this->attributeModel->loadByCode('catalog_product', 'supplier')->getAttributeId()
        )
        ->setStoreFilter()
        ->load();
        
        foreach ($attributeOptionAll->getData() as $attributeOption) {
            if ($attributeOption['option_id'] == $supplierOptionId) {
                return $attributeOption['default_value'];
            }
        }
        
        return "";
        
    }
}