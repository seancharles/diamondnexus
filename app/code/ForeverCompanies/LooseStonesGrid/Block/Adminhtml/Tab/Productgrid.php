<?php

namespace ForeverCompanies\LooseStonesGrid\Block\Adminhtml\Tab;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use ForeverCompanies\LooseStonesGrid\Model\GridModel;

class Productgrid extends \Magento\Backend\Block\Widget\Grid\Extended
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
        GridModel $grid,
        Visibility $visibility = null,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        $this->productCollFactory = $productCollFactory;
        $this->coreRegistry = $coreRegistry;
        $this->moduleManager = $moduleManager;
        $this->_storeManager = $storeManager;
        $this->visibility = $visibility ?: ObjectManager::getInstance()->get(Visibility::class);
        
        $this->gridModel = $grid;
        
        parent::__construct($context, $backendHelper, $data);
    }
    /**
     * [_construct description]
     * @return [type] [description]
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('loose_stones_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getRequest()->getParam('entity_id')) {
            $this->setDefaultFilter(['in_products' => 1]);
        } else {
            $this->setDefaultFilter(['in_products' => 0]);
        }
        $this->setSaveParametersInSession(true);
    }
    /**
     * [get store id]
     *
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
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
    
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $store = $this->_getStore();
        
        $this->addColumn(
            'in_products',
            [
                'type' => 'checkbox',
                'html_name' => 'products_id',
                'required' => true,
                'values' => $this->_getSelectedProducts(),
                'align' => 'center',
                'index' => 'entity_id',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Cert #'),
                'index' => 'sku',
                'header_css_class' => 'col-sku',
                'column_css_class' => 'col-sku',
            ]
        );
        $this->addColumn(
            'supplier',
            [
                'header' => __('Supplier'),
                'index' => 'supplier',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('supplier')
            ]
        );
        $this->addColumn(
            'filter_ship_date',
            [
                'header' => __('Delivery Date'),
                'index' => 'filter_ship_date'
            ]
        );
        $this->addColumn(
            'rapaport',
            [
                'header' => __('Rapaport'),
                'index' => 'rapaport'
            ]
        );
        $this->addColumn(
            'pct_off_rap',
            [
                'header' => __('Rap %'),
                'index' => 'pct_off_rap'
            ]
        );
        $this->addColumn(
            'msrp',
            [
                'header' => __('MSRP'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'msrp',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'custom_price',
            [
                'header' => __('Custom Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'custom_price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'cost',
            [
                'header' => __('Cost'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'cost',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'custom_cost',
            [
                'header' => __('Custom Cost'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'custom_cost',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price',
            ]
        );
        $this->addColumn(
            'cert_url_key',
            [
                'header' => __('Cert'),
                'index' => 'cert_url_key'
            ]
        );
        $this->addColumn(
            'diamond_img_url',
            [
                'header' => __('Image'),
                'index' => 'diamond_img_url'
            ]
        );
        $this->addColumn(
            'video_url',
            [
                'header' => __('Video'),
                'index' => 'video_url'
            ]
        );
        $this->addColumn(
            'online',
            [
                'header' => __('Online'),
                'index' => 'online',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('online')
            ]
        );
        $this->addColumn(
            'lab',
            [
                'header' => __('Lab'),
                'index' => 'lab'
            ]
        );
        $this->addColumn(
            'shape',
            [
                'header' => __('Shape'),
                'index' => 'shape',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('shape')
            ]
        );
        $this->addColumn(
            'color',
            [
                'header' => __('Color'),
                'index' => 'color',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('color')
            ]
        );
        $this->addColumn(
            'clarity',
            [
                'header' => __('Clarity'),
                'index' => 'clarity',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('clarity')
            ]
        );
        $this->addColumn(
            'cut_grade',
            [
                'header' => __('Cut'),
                'index' => 'cut_grade',
                'type' => 'options',
                'options' => $this->gridModel->getOptions('cut_grade')
            ]
        );
        $this->addColumn(
            'stone_carat',
            [
                'header' => __('Carat'),
                'index' => 'stone_carat'
            ]
        );
        $this->addColumn(
            'country_of_manufacture',
            [
                'header' => __('Origin'),
                'index' => 'country_of_manufacture'
            ]
        );
        $this->addColumn(
            'country_of_manufacture',
            [
                'header' => __('Origin'),
                'index' => 'country_of_manufacture'
            ]
        );
        $this->addColumn(
            'country_of_manufacture',
            [
                'header' => __('Origin'),
                'index' => 'country_of_manufacture'
            ]
        );
        $this->addColumn(
            'length_to_width',
            [
                'header' => __('Aspect'),
                'index' => 'length_to_width'
            ]
        );
        $this->addColumn(
            'length_to_width',
            [
                'header' => __('Aspect'),
                'index' => 'length_to_width'
            ]
        );
        $this->addColumn(
            'aaa',
            [
                'header' => __('Measurements'),
                'index' => 'aaa'
            ]
        );
        $this->addColumn(
            'polish',
            [
                'header' => __('Polish'),
                'index' => 'polish'
            ]
        );
        $this->addColumn(
            'symmetry',
            [
                'header' => __('Symmetry'),
                'index' => 'symmetry'
            ]
        );
        $this->addColumn(
            'girdle',
            [
                'header' => __('Girdle'),
                'index' => 'gidle'
            ]
        );
        $this->addColumn(
            'fluor',
            [
                'header' => __('Fluor'),
                'index' => 'fluor'
            ]
        );
        $this->addColumn(
            'as_grown',
            [
                'header' => __('As Grown'),
                'index' => 'as_grown'
            ]
        );
        $this->addColumn(
            'born_on_date',
            [
                'header' => __('Born on Date'),
                'index' => 'born_on_date'
            ]
        );
        $this->addColumn(
            'carbon_neutral',
            [
                'header' => __('Carbon Neutral'),
                'index' => 'carbon_neutral'
            ]
        );
        $this->addColumn(
            'blockchain_verified',
            [
                'header' => __('Blockchain Verified'),
                'index' => 'blockchain_verified'
            ]
        );
        $this->addColumn(
            'charitable_contribution',
            [
                'header' => __('Charitable Contribution'),
                'index' => 'charitable_contribution'
            ]
        );
        $this->addColumn(
            'cvd',
            [
                'header' => __('CVD'),
                'index' => 'cvd'
            ]
        );
        $this->addColumn(
            'hpht',
            [
                'header' => __('HPHT'),
                'index' => 'hpht'
            ]
        );
        $this->addColumn(
            'patented',
            [
                'header' => __('Patented'),
                'index' => 'patented'
            ]
        );
        $this->addColumn(
            'custom',
            [
                'header' => __('Custom'),
                'index' => 'custom'
            ]
        );
        $this->addColumn(
            'color_of_colored_diamonds',
            [
                'header' => __('Colored Color'),
                'index' => 'color_of_colored_diamonds'
            ]
        );
        $this->addColumn(
            'hue',
            [
                'header' => __('Hue'),
                'index' => 'hue'
            ]
        );
        $this->addColumn(
            'intensity',
            [
                'header' => __('Intensity'),
                'index' => 'intensity'
            ]
        );
        
        return parent::_prepareColumns();
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/stones', ['_current' => true]);
    }
    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }
    /**
     * @return array
     */
    public function getSelectedProducts()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $model = $this->productCollFactory->create()->addFieldToFilter('entity_id', $id);
        $grids = [];
        foreach ($model as $key => $value) {
            $grids[] = $value->getProductId();
        }
        $prodId = [];
        foreach ($grids as $obj) {
            $prodId[$obj] = ['position' => "0"];
        }
        return $prodId;
    }
}