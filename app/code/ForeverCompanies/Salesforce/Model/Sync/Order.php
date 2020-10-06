<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use ForeverCompanies\Salesforce\Model\ReportFactory as ReportFactory;
use ForeverCompanies\Salesforce\Model\Connector;
use ForeverCompanies\Salesforce\Model\Data;
use Magento\Sales\Model\OrderFactory;

class Order extends Connector
{
    const SALESFORCE_ORDER_ATTRIBUTE_CODE = 'salesforce_order_id';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var array
     */
    protected $existedOrders = [];

    /**
     * @var array
     */
    protected $createOrderIds = [];

    protected $dataGetter;

    /**
     * Order constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param OrderFactory $orderFactory
     * @param DataGetter $dataGetter
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        Data $data,
        OrderFactory $orderFactory,
        DataGetter $dataGetter,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory
    )
    {
        parent::__construct($scopeConfig, $resourceConfig, $reportFactory, $queueFactory, $requestLogFactory);
        $this->orderFactory  = $orderFactory;
    }
}
