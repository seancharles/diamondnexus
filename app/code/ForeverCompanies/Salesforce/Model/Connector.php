<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class Connector
 *
 * @package ForeverCompanies\Salesforce\Model
 */
class Connector
{
    /**
     *#@+
     * Constants
     */
    const XML_PATH_SALESFORCE_IS_CONNECTED = 'salesforcecrm/salesforceconfig/is_connected';
    const XML_PATH_SALESFORCE_AUTH_URL = 'salesforcecrm/salesforceconfig/auth_url';
    const XML_PATH_SALESFORCE_CLIENT_HOST = 'salesforcecrm/salesforceconfig/host';
    const XML_PATH_SALESFORCE_CLIENT_ID = 'salesforcecrm/salesforceconfig/client_id';
    const XML_PATH_SALESFORCE_CLIENT_SECRET = 'salesforcecrm/salesforceconfig/client_secret';
    const XML_PATH_SALESFORCE_EMAIL = 'salesforcecrm/salesforceconfig/email';
    const XML_PATH_SALESFORCE_PASSWD = 'salesforcecrm/salesforceconfig/passwd';
    const XML_PATH_SALESFORCE_ACCESS_TOKEN = 'salesforcecrm/salesforceconfig/access_token';
    const XML_PATH_SALESFORCE_INSTANCE_URL = 'salesforcecrm/salesforceconfig/instance_url';
    const XML_PATH_SALESFORCE_ORDER_ENABLE = 'salesforcecrm/sync/order';
    const XML_PATH_SALESFORCE_ACCOUNT_ENABLE = 'salesforcecrm/sync/account';

    const SF_CUSTOMER_TYPE = 'Customer';
    const SF_ORDER_TYPE = 'Order';

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var string
     */
    protected $_type;
    
    /**
     * \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;
	
    /**
     * \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Connector constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
		TypeListInterface $cacheTypeList,
        ResourceModelConfig $resourceConfig,
        RequestLogFactory $requestLogFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
		$this->configWriter = $configWriter;
		$this->cacheTypeList = $cacheTypeList;
        $this->_resourceConfig = $resourceConfig;
        $this->_requestLogFactory = $requestLogFactory;
    }

    /**
     * Get Access Token & Instance Url
     *
     * @param array $data
     * @param bool|false $update
     * @return mixed
     */
    public function getAccessToken(array $data = [], $update = false)
    {
        try {
            if (!empty($data) && $update) {
                $host = $data['host'];
                $username = $data['username'];
                $password = $data['password'];
                $client_id = $data['client_id'];
                $client_secret = $data['client_secret'];
            } else {
                $host  = $this->_scopeConfig->getValue(
                    self::XML_PATH_SALESFORCE_CLIENT_HOST
                );
                $username  = $this->_scopeConfig->getValue(
                    self::XML_PATH_SALESFORCE_EMAIL
                );
                $password = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_PASSWD);
                $client_id = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_CLIENT_ID);
                $client_secret = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_CLIENT_SECRET);
            }

            if (!$username || !$password || !$client_id || !$client_secret) {
                throw new \InvalidArgumentException('Field not setup !');
            }

            $url = parse_url($host);

            $auth_url = $this->_scopeConfig->getValue(self::XML_PATH_SALESFORCE_AUTH_URL);
            
            $params =[
                'grant_type' => 'password',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'username' => $username,
                'password' => $password
            ];
            $response = $this->makeRequest(\Zend_Http_Client::POST, $auth_url, [], $params);
            
            $response = \GuzzleHttp\json_decode($response, true);

            if (isset($response['access_token']) && isset($response['instance_url'])) {
				$this->configWriter->save(
					self::XML_PATH_SALESFORCE_INSTANCE_URL,
					$response['instance_url']
				);
				$this->configWriter->save(
					self::XML_PATH_SALESFORCE_ACCESS_TOKEN,
					$response['access_token']
				);
				$this->configWriter->save(
					self::XML_PATH_SALESFORCE_IS_CONNECTED,
					1
				);
				
				// if the config is updated the cache needs to be flushed
				// otherwise the updated key value will not be refreshed next run
				$this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);

                //$response['account_id'] = $response['id'];
                unset($response['id']);
                unset($response['token_type']);
                unset($response['signature']);
                unset($response['issued_at']);

                return $response;
            } else {
                throw new \InvalidArgumentException($response['error_description']);
            }
        } catch (\InvalidArgumentException $exception) {
            echo 'Exception Message: ' . $exception->getMessage() . '<br/>';
            return $exception->getMessage();
        }
    }

    /**
     * Send request to Salesforce
     *
     * @param  $method
     * @param  $path
     * @param  null $parameter
     * @return mixed|string
     */
    public function sendRequest($method, $path, $parameter = null, $useFreshCredential = false)
    {
        if ($useFreshCredential == false) {
            $instance_url = $this->_scopeConfig->getValue(
                self::XML_PATH_SALESFORCE_INSTANCE_URL,
                ScopeInterface::SCOPE_STORE
            );
            $access_token = $this->_scopeConfig->getValue(
                self::XML_PATH_SALESFORCE_ACCESS_TOKEN,
                ScopeInterface::SCOPE_STORE
            );
        }
		
        try {
            if (!isset($instance_url) || !isset($access_token) || $useFreshCredential) {
                $login = $this->getAccessToken();
                
                if(isset($login['instance_url']) === true && isset($login['access_token']) === true) {
                    $instance_url = $login['instance_url'];
                    $access_token = $login['access_token'];
                } else {
                    echo "Unable to authenticate to salesforce. Please check credentials.";
                    return;
                }
            }
        } catch (\InvalidArgumentException $exception) {
            echo 'Exception Message: ' . $exception->getMessage();
            return $exception->getMessage();
        }

        $headers = [
            "Authorization" => "Bearer "  .$access_token,
            "Content-Type" => "application/json",
        ];
        $url = $instance_url . $path;
        $response = $this->makeRequest($method, $url, $headers, $parameter);
        $response = json_decode($response, true);
		
        if (isset($response[0]['errorCode']) && $response[0]['errorCode'] == 'INVALID_SESSION_ID') {
            $response = $this->sendRequest($method, $path, $parameter, true);
        }

        return $response;
    }

    /**
     * Create new Record in Salesforce
     *
     * @param string $table
     * @param array $parameter
     * @return string or false
     */
    public function createRecords($table, $parameter, $mid = null)
    {
        $path = "/services/data/v34.0/sobjects" . $table . "/";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        if (isset($response["id"])) {
            $id = $response["id"];
            return $id;
        }

        return false;
    }

    /**
     * Create new Order in Salesforce
     *
     * @param array $parameter
     */
    public function createOrder($parameter)
    {
        $path = "/services/apexrest/createOrder";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        
        if (isset($response["orderId"])) {
            $id = $response["orderId"];
            return $id;
        }

        return false;
    }

    /**
     * Create new Order in Salesforce
     *
     * @param array $parameter
     */
    public function createGuestOrder($parameter)
    {
        $path = "/services/apexrest/createGuestOrder";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        
        if (isset($response["orderId"])) {
            $id = $response["orderId"];
            return $id;
        }

        return false;
    }

    /**
     * Update new Order in Salesforce
     *
     * @param array $parameter
     */
    public function updateOrder($parameter)
    {
        $path = "/services/apexrest/updateOrder";
        $response = $this->sendRequest(\Zend_Http_Client::PUT, $path, $parameter);
        
        if (isset($response["status"])) {
            $status = $response["status"];
            if ($status == "success") {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Create new Order in Salesforce
     *
     * @param array $parameter
     */
    public function createOrderLine($parameter)
    {
        $path = "/services/apexrest/createLine";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        
        if (isset($response["lineId"])) {
            $id = $response["lineId"];
            return $id;
        }

        return false;
    }
    
    /**
     * Create new Order in Salesforce
     *
     * @param array $parameter
     */
    public function updateOrderLine($parameter)
    {
        $path = "/services/apexrest/updateLine";
        $response = $this->sendRequest(\Zend_Http_Client::PUT, $path, $parameter);
        
        if (isset($response["orderId"])) {
            $id = $response["orderId"];
            return $id;
        }

        return false;
    }
    
    /**
     * Create new Order in Salesforce
     *
     * @param array $parameter
     */
    public function clearOrderLines($parameter)
    {
        $path = "/services/apexrest/deleteLine";
        $response = $this->sendRequest(\Zend_Http_Client::DELETE, $path, $parameter);
        
        if (isset($response["orderId"])) {
            $id = $response["orderId"];
            return $id;
        }

        return false;
    }

    /**
     * Create new Account in Salesforce
     *
     * @param array $parameter
     */
    public function createAccount($parameter)
    {
        $path = "/services/apexrest/createAccount";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        
        print_r($response);
        
        if (isset($response["acctId"])) {
            $id = $response["acctId"];
            return $response;
        }

        return false;
    }
    
    /**
     * Create new Lead in Salesforce
     *
     * @param array $parameter
     */
    public function createLead($parameter)
    {
        $path = "/services/apexrest/createLead";
        $response = $this->sendRequest(\Zend_Http_Client::POST, $path, $parameter);
        
        if (isset($response["leadId"])) {
            $id = $response["leadId"];
            return $response;
        }

        return false;
    }
    
    /**
     * Create new Lead in Salesforce
     *
     * @param array $parameter
     */
    public function updateLead($parameter)
    {
        $path = "/services/apexrest/updateLead";
        $response = $this->sendRequest(\Zend_Http_Client::PUT, $path, $parameter);
		
        if (isset($response["status"]) == true && $response["status"] == "success") {
            return true;
        }

        return false;
    }


    /**
     * Update a account in Salesforce
     *
     * @param array $parameter
     */
    public function updateAccount($parameter)
    {
        $path = "/services/apexrest/updateAccount";
        $this->sendRequest(\Zend_Http_Client::PUT, $path, $parameter);
    }

    /**
     * Search records in Salesforce
     *
     * @param string $table
     * @param string $field
     * @param string $value
     * @return string or false
     */
    public function searchRecord($table, $field, $value)
    {
        $query = "SELECT Id FROM $table WHERE $field = '$value'";
        $query .= ' LIMIT 1';
        $path = '/services/data/v34.0/query?q=' . urlencode($query);

        $response = $this->sendRequest(\Zend_Http_Client::GET, $path);
        
        print_r($query);
        
        print_r($response);
        
        if(isset($response['records'][0]) == true) {
            // pull the first row from the response
            $row = $response['records'][0];
            
            // return the id for each type
            if(($table == 'Order' || $table == 'Lead' || $table == 'Account') && isset($row['Id']) == true) {
                return $row['Id'];
            }
        }

        return false;
    }

    /**


    /**
     * Get All Field of a table in Salesforce
     *
     * @param  string $table
     * @return string
     */
    public function getFields($table)
    {
        $path = '/services/data/v34.0/sobjects/' . $table.  '/describe/';
        $response = $this->sendRequest(\Zend_Http_Client::GET, $path);
        $data = [];
        $_type = [
            'picklist',
            'date',
            'datetime',
            'reference',
        ];
        if (isset($response['fields'])) {
            foreach ($response['fields'] as $item => $value) {
                $type = $value['type'];
                if ($value['permissionable'] == 1 && !in_array($type, $_type)) {
                    $label = $value['label'];
                    $name = $value['name'];
                    $data[$name] = $label;
                }
            }
        }

        $fields = serialize($data);

        return $fields;
    }

    /**
     * @param $method
     * @param $url
     * @param array $headers
     * @param array $params
     * @return string
     * @throws \Zend_Http_Client_Exception
     */
    public function makeRequest($method, $url, $headers = [], $params = [])
    {
        $client = new \Zend_Http_Client($url);
        $client->setHeaders($headers);
        if ($method != \Zend_Http_Client::GET) {
            $client->setParameterPost($params);
            if (isset($headers['Content-Type']) && $headers['Content-Type'] == 'application/json') {
                $client->setEncType('application/json');
                $params = \GuzzleHttp\json_encode($params);
                $client->setRawData($params);
            }
        }
        $response = $client->request($method)->getBody();

        //print_r($response);

        $this->_requestLogFactory->create()->addRequest(RequestLog::REST_REQUEST_TYPE);
        return $response;
    }

}
