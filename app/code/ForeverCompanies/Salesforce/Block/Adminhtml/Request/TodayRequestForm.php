<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\Request;

use ForeverCompanies\Salesforce\Model\RequestLog;
use Magento\Sales\Model\Order;

/**
 * Class TodayRequestForm
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Request
 */
class TodayRequestForm extends \Magento\Backend\Block\Widget
{
    /**
     * @var \ForeverCompanies\Salesforce\Model\ReportFactory
     */
    protected $logFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\RequestLogFactory
     */
    protected $requestLogFactory;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Reader
     */
    protected $configReader;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \ForeverCompanies\Salesforce\Model\ReportFactory $logFactory,
        \ForeverCompanies\Salesforce\Model\RequestLogFactory $requestLogFactory,
        \Magento\Framework\App\DeploymentConfig\Reader $configReader,
        array $data = []
    ) {
        $this->configReader = $configReader;
        $this->requestLogFactory = $requestLogFactory;
        $this->logFactory = $logFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getTodayRestRequest()
    {
        return $this->getTodayRequest(RequestLog::REST_REQUEST_TYPE);
    }

    /**
     * @return int
     */
    public function getTodayBulkRequest()
    {
        return $this->getTodayRequest(RequestLog::BULK_REQUEST_TYPE);
    }

    /**
     * @param String $type
     * @return int
     */
    protected function getTodayRequest($type)
    {
        $requestLog = $this->requestLogFactory->create()->getCollection()
            ->addFieldToFilter('date', date('Y-m-d'))
            ->getFirstItem();
        $column = $type.'_request';
        return $requestLog->getData($column);
    }

    public function getTodayOrderRequest()
    {
        return $this->getTodayItemRequest('Order');
    }

    protected function getTodayItemRequest($itemType)
    {
        $log = $this->logFactory->create()->getCollection();
        $log->addFieldToFilter('datetime', ['gteq' => date('Y-m-d')])
            ->getSelect()
            ->columns(['COUNT(id) as count'])
            ->group('salesforce_table')
            ->having('salesforce_table="'.$itemType.'"');
        foreach ($log as $result) {
            return $result->getData('count');
        }
        return false;
    }

}
