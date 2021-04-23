<?php

namespace ForeverCompanies\LooseStoneImport\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Review\Model\Review;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;



class StoneImport
{    
    protected $storeRepository;
    protected $storeManager;
    protected $productCollectionFactory;
    protected $productModel;
    protected $resourceConnection;
    protected $attributeSetMod;
    protected $stockItemModel;
    protected $reviewModel;
    protected $reviewCollection;
    
    public function __construct(
        CollectionFactory $collectionFactory,
        ProductFactory $productFactory,
        ResourceConnection $resource,
        AttributeSetRepositoryInterface $attributeSetRepo,
        StockItemRepository $stockItemRepo,
        Review $rev,
        ReviewCollectionFactory $coll
        ) {
            $this->productCollectionFactory = $collectionFactory;
            $this->productModel = $productFactory;
            $this->resourceConnection = $resource;
            $this->attributeSetMod = $attributeSetRepo;
            $this->stockItemModel = $stockItemRepo;
            $this->reviewModel = $rev;
            $this->reviewCollection = $coll;
    }
    
    function run()
    {
        
    }
    
}