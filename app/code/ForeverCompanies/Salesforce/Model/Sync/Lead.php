<?php

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use ForeverCompanies\Salesforce\Model\Connector;
use ForeverCompanies\Salesforce\Model\Data;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;

class Lead extends Connector
{
    /**
     * @var Data
     */
    protected $data;
    protected $_type;

    /**
     * Order constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param RequestLogFactory $requestLogFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
		TypeListInterface $cacheTypeList,
        ResourceModelConfig $resourceConfig,
        Data $data,
        RequestLogFactory $requestLogFactory,
        \Magento\Config\Model\Config $configModel
    ) {
        parent::__construct(
            $scopeConfig,
			$configWriter,
			$cacheTypeList,
            $resourceConfig,
            $requestLogFactory,
            $configModel
        );
        $this->_type = 'Lead';
        $this->data = $data;
    }

    /**
     * Create new lead in Salesforce
     *
     * @param  array     $data
     * @return string
     */
    public function create($data)
    {
        return $this->createLead($data);
    }
    
    /**
     * Update lead in Salesforce $data['Id'] = $updateLeadId;
     *
     * @param  array     $data
     * @return string
     */
    public function update($data, $leadId = false)
    {
		if(isset($data['lead']['Id']) == false) {
			return false;
		}
		
        return $this->updateLead($data);
    }
}
