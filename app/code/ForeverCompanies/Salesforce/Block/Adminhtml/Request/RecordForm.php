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
use Magento\Backend\Block\Widget;
use Magento\Backend\Block\Template\Context;

/**
 * Class RecordForm
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Request
 */
class RecordForm extends Widget
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
     * @var string
     */
    protected $highestDay;

    /**
     * @var string
     */
    protected $lowestDay;

    /**
     * QueryForm constructor.
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

    public function getHighestRequestDay()
    {
        if (!$this->highestDay) {
            $this->highestDay = $this->getRecordDay('DESC');
        }
        return $this->highestDay;
    }

    public function getLowestRequestDay()
    {
        if (!$this->lowestDay) {
            $this->lowestDay = $this->getRecordDay('ASC');
        }
        return $this->lowestDay;
    }

    protected function getRecordDay($order)
    {
        $requestLog = $this->requestLogFactory->create()->getCollection()
            ->addOrder('rest_request', $order)
            ->addOrder('bulk_request', $order)
            ->getFirstItem();
        return $requestLog->getData('date');
    }

    public function getHighestRestRequest()
    {
        return $this->getRequestRecord(RequestLog::REST_REQUEST_TYPE, $this->getHighestRequestDay());
    }

    public function getLowestRestRequest()
    {
        return $this->getRequestRecord(RequestLog::REST_REQUEST_TYPE, $this->getLowestRequestDay());
    }


    protected function getRequestRecord($type, $date)
    {
        $type = $type . '_request';
        $requestLog = $this->requestLogFactory->create()->getCollection()
            ->addFieldToFilter('date', $date)
            ->getFirstItem();
        return $requestLog->getData($type);
    }
}
