<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Framework\Xml\Parser;

class BullConnector
{
    /**
     *#@+
     * Constants
     */
    const XML_PATH_SALESFORCE_EMAIL          = 'salesforcecrm/config/email';
    const XML_PATH_SALESFORCE_PASSWD         = 'salesforcecrm/config/passwd';
    const XML_PATH_SALESFORCE_SECURITY_TOKEN = 'salesforcecrm/config/security_token';
    const XML_PATH_SALESFORCE_INSTANCE_URL   = 'salesforcecrm/config/instance_url';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var RequestLogFactory
     */
    protected $requestLogFactory;

    /**
     * BulkConnector constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestLogFactory $requestLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestLogFactory $requestLogFactory
    ) {
        $this->scopeConfig    = $scopeConfig;
        $this->requestLogFactory = $requestLogFactory;
    }

    /**
     * @return string
     */
    public function _getAccessToken()
    {
        try {
            $xml = '<?xml version="1.0" encoding="utf-8" ?>';
            $xml .= '<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                     xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                     xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">';
            $xml .= '<env:Body>';
            $xml .= '<n1:login xmlns:n1="urn:partner.soap.sforce.com">';
            $xml .= '<n1:username>' .
                     $this->scopeConfig->getValue(self::XML_PATH_SALESFORCE_EMAIL) .
                    '</n1:username>';
            $xml .= '<n1:password>' .
                    $this->scopeConfig->getValue(self::XML_PATH_SALESFORCE_PASSWD).
                    $this->scopeConfig->getValue(self::XML_PATH_SALESFORCE_SECURITY_TOKEN)
                    .'</n1:password>';
            $xml .= '</n1:login>';
            $xml .= '</env:Body>';
            $xml .= '</env:Envelope>';
            $xml = trim($xml);
            $url = 'https://login.salesforce.com/services/Soap/u/38.0';
            $headers = [
                'Content-Type' => 'text/xml',
                'charset' => 'UTF-8',
                'SOAPAction' => 'login'
            ];

        } catch (\Exception $e) {
            echo 'Exception Message: ' . $e->getMessage() . '<br/>';
            return $e->getMessage();
        }
    }

    protected function sendRequest($url, $method, $headers = [], $params = '')
    {

    }

    protected function parseXml($xml)
    {
        try {
            $parser = new Parser();
            $parser->loadXML($xml);
            return $parser->getDom();
        } catch (\Exception $e){

        }
        return false;
    }

    public function getAccessToken()
    {
        if (!$this->sessionId){
            $this->_getAccessToken();
        }
        return $this->sessionId;
    }
}
