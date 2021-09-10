<?php
namespace ForeverCompanies\AdminOrderFixes\Block\Adminhtml\Order\Create\Search;

use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid as OrigGrid;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\DataProvider\ProductCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Visibility;

class Grid extends OrigGrid
{
    protected $catalogConfig;
    protected $productCollectionProvider;
    protected $productVisibility;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\Config $salesConfig,
        Visibility $productV,
        array $data = [],
        ProductCollection $productCollectionProvider = null
        ) {
            $this->catalogConfig = $catalogConfig;
            $this->productCollectionProvider = $productCollectionProvider ?: ObjectManager::getInstance()->get(ProductCollection::class);
            $this->productVisibility = $productV;
            
            parent::__construct(
                $context,
                $backendHelper,
                $productFactory,
                $catalogConfig,
                $sessionQuote,
                $salesConfig,
                $data
            );
    }
    
    protected function _prepareCollection()
    {
        $attributes = $this->catalogConfig->getProductAttributes();
        $store = $this->getStore();
        
        $collection = $this->productCollectionProvider->getCollectionForStore($store);
        $collection->addAttributeToSelect(
            $attributes
        );
        $collection->addAttributeToFilter(
            'type_id',
            $this->_salesConfig->getAvailableProductTypes()
        );
        
        $collection->addAttributeToFilter('status', array('eq' =>  1));
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        
        $this->setCollection($collection);
        return \Magento\Backend\Block\Widget\Grid\Extended::_prepareCollection();
    }
}