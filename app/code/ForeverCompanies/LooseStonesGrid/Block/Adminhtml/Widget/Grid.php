<?php 

namespace ForeverCompanies\Block\Adminhtml\Widget;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    
    protected $productCollFactory;
    protected $gridModel;
    /**
     * @param \Magento\Backend\Block\Template\Context    $context
     * @param \Magento\Backend\Helper\Data               $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory      $productFactory
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param \Magento\Framework\Module\Manager          $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Visibility|null                            $visibility
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
  //      GridModel $grid,
        Visibility $visibility = null,
        array $data = []
        ) {
            $this->productFactory = $productFactory;
            $this->productCollFactory = $productCollFactory;
            $this->coreRegistry = $coreRegistry;
            $this->moduleManager = $moduleManager;
            $this->_storeManager = $storeManager;
            $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
            
  //          $this->gridModel = $grid;
            
            parent::__construct($context, $backendHelper, $data);
    }
    
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->productFactory->create()->getCollection()
        ->addAttributeToSelect(
            '*'
            )->setStore(
                $store
                )->addAttributeToFilter(
                    'attribute_set_id',
                    '31'
                    );
                
                if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
                    $collection->joinField(
                        'qty',
                        'cataloginventory_stock_item',
                        'qty',
                        'product_id=entity_id',
                        '{{table}}.stock_id=1',
                        'left'
                        );
                }
                if ($store->getId()) {
                    $collection->setStoreId($store->getId());
                    $collection->addStoreFilter($store);
                    $collection->joinAttribute(
                        'name',
                        'catalog_product/name',
                        'entity_id',
                        null,
                        'inner',
                        Store::DEFAULT_STORE_ID
                        );
                    $collection->joinAttribute(
                        'status',
                        'catalog_product/status',
                        'entity_id',
                        null,
                        'inner',
                        $store->getId()
                        );
                    $collection->joinAttribute(
                        'visibility',
                        'catalog_product/visibility',
                        'entity_id',
                        null,
                        'inner',
                        $store->getId()
                        );
                    $collection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $store->getId());
                } else {
                    $collection->addAttributeToSelect('price');
                    $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
                    $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
                }
                $this->setCollection($collection);
                return parent::_prepareCollection();
    }
}