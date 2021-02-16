<?php

namespace ForeverCompanies\CustomAdmin\Block\Adminhtml\Customer\Edit\Tab\View;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Registry;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Adminhtml customer recent orders grid block
 */
class CommentHistory extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Grid\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $collectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        Registry $coreRegistry,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->_logger = $logger;
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize the orders grid.
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customadmin_view_comment_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }
    /**
     * {@inheritdoc}
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize(50)->setCurPage(1);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {

        $collection = $this->collectionFactory->create();
        //$collectionOrder = $this->orderCollectionFactory->create();
        $collection->getSelect()->joinInner(
            ['order' => 'sales_order'],
            'main_table.parent_id = order.entity_id',
            [
                'customer_id' => 'order.customer_id',
            ]
        );
        $customerId = $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        $collection->addFieldToFilter('customer_id', $customerId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order ID'),
                'index' => 'parent_id',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created at'),
                'index' => 'created_at',
            ]
        );
        $this->addColumn(
            'comment',
            [
                'header' => __('Comment'),
                'index' => 'comment',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get headers visibility
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }
}
