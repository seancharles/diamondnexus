<?php

namespace ForeverCompanies\QuoteCleaner\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Cleaner helper class
 */
class Cleaner extends AbstractHelper
{
    const CONFIG_XML_OLDER_THAN = 'quote_cleaner/quote_cleaner/clean_quoter_older_than';
    const CONFIG_XML_ANONYMOUS_OLDER_THAN = 'quote_cleaner/quote_cleaner/clean_anonymous_quotes_older_than';
    const CONFIG_XML_LIMIT = 'quote_cleaner/quote_cleaner/limit';
    const CONFIG_XML_CRON = 'quote_cleaner/quote_cleaner/cron';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $limit;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var null
     */
    private $startTime = null;

    /**
     * @var null
     */
    private $endTime = null;

    /**
     * @var array
     */
    private $report = [];

    /**
     * @var int
     */
    private $customerOlderThan;

    /**
     * @var string
     */
    private $tableName;

    /**
     * Cleaner constructor.
     * @param Context $context
     * @param LoggerInterface $logger
     * @param ResourceConnection $resource
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        ResourceConnection $resource,
        OrderRepositoryInterface $orderRepositoryInterface,
        OrderFactory $orderFactory
    ) {
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->orderRepository = $orderRepositoryInterface;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * Get quotes older than
     * @return string
     */
    public function getQuotesOlderThan()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_OLDER_THAN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get anonymous quotes older than
     * @return string
     */
    public function getAnonymousQuotesOlderThan()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_ANONYMOUS_OLDER_THAN,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get limit
     * @return string
     */
    public function getLimit()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get cron
     * @return string
     */
    public function getCron()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_XML_CRON,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Initiate variables
     * @return void
     */
    public function initiate()
    {
        $this->limit = min($this->getLimit(), 50000);
        $this->customerOlderThan = max($this->getQuotesOlderThan(), 7);
        $anonymousOlderThan = max($this->getAnonymousQuotesOlderThan(), 7);
        $this->tableName = $this->getTableName();
        $this->startTime = time();
        $this->report = [];
    }

    /**
     * Clean customer quotes
     * @return array
     */
    public function cleanCustomerQuotes()
    {
        $this->initiate();
        $select = $this->prepareSelect('NOT ISNULL(main_table.customer_id) AND main_table.customer_id != 0');
        return $this->cancelStatus($select);
    }

    /**
     * Clean anonymous quotes
     * @return array
     */
    public function cleanAnonymousQuotes()
    {
        $this->initiate();
        $select = $this->prepareSelect('ISNULL(main_table.customer_id) OR main_table.customer_id = 0');
        return $this->cancelStatus($select);
    }

    /**
     * @param $select
     * @return array
     */
    public function cancelStatus($select)
    {
        $this->report['quote_duration'] = time() - $this->startTime;
        $items = $select->getConnection()->fetchAll($select);
        foreach ($items as $item) {
            $order = $this->orderRepository->get($item['order_id']);
            $order->setStatus(Order::STATE_CANCELED)->setState(Order::STATE_CANCELED);
            $this->orderRepository->save($order);
        }
        $this->report['quote_count'] = count($items);
        return $this->report;
    }

    /**
     * Get quote table name
     * @return string
     */
    public function getTableName()
    {
        return $this->resource->getTableName('quote');
    }

    /**
     * @param $where
     * @return Select
     */
    protected function prepareSelect($where)
    {
        return $this->connection
            ->select()
            ->from(['main_table' => $this->tableName])
            ->joinInner(
                ['store' => 'store'],
                'main_table.store_id = store.store_id'
            )->joinInner(
                ['sales' => $this->connection->getTableName('sales_order')],
                'main_table.entity_id = sales.quote_id',
                ['order_id' => 'sales.entity_id']
            )->where($where)
            ->where('main_table.updated_at < DATE_SUB(NOW(), INTERVAL ? DAY)', $this->customerOlderThan)
            ->where('sales.status = "quote"')
            ->limit($this->limit);
    }
}
