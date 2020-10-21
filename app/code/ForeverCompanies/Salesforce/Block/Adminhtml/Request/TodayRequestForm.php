<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Block\Adminhtml\Request;

use ForeverCompanies\Salesforce\Model\RequestLog;
use ForeverCompanies\Salesforce\Model\ReportFactory;
use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use Magento\Framework\App\DeploymentConfig\Reader;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;

/**
 * Class TodayRequestForm
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Request
 */
class TodayRequestForm extends Widget
{
    /**
     * @var ReportFactory
     */
    protected $logFactory;

    /**
     * @var RequestLogFactory
     */
    protected $requestLogFactory;

    /**
     * @var Reader
     */
    protected $configReader;

    /**
     * Form constructor.
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        ReportFactory $logFactory,
        RequestLogFactory $requestLogFactory,
        Reader $configReader,
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
