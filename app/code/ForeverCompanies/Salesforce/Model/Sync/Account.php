<?php

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use ForeverCompanies\Salesforce\Model\Connector;
use ForeverCompanies\Salesforce\Model\Data;
use Magento\Config\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Customer\Model\CustomerFactory;

class Account extends Connector
{
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'sf_acctid';

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $data;
    protected $_type;

    /**
     * Order constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param TypeListInterface $cacheTypeList
     * @param ResourceModelConfig $resourceConfig
     * @param Data $data
     * @param RequestLogFactory $requestLogFactory
     * @param Config $configModel
     * @param CustomerFactory $customerFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
		TypeListInterface $cacheTypeList,
        ResourceModelConfig $resourceConfig,
        Data $data,
        RequestLogFactory $requestLogFactory,
        Config $configModel,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $scopeConfig,
			$configWriter,
			$cacheTypeList,
            $resourceConfig,
            $requestLogFactory,
            $configModel
        );
        $this->customerFactory = $customerFactory;
        $this->_type = 'Account';
        $this->data = $data;
    }

    /**
     * Create or update new Account in Salesforce
     *
     * @param $magAccountId
     * @param bool $sfAccountId
     * @return string
     */
    public function sync($magAccountId, $sfAccountId = false)
    {
        $customer = $this->customerFactory->create()->load($magAccountId);
        
        $id = null;
        
        $data  = $this->data->getCustomer($customer, $this->_type);

        $params = [
            'Web_Account_Id__c' => $data['entity_id'],
            'FirstName' => $data['firstname'],
            'LastName' => $data['lastname'],
            'PersonEmail' => $data['email'],
            'BillingCity' => $data['bill_city'],
            'BillingState' => $data['bill_region'],
            'BillingCountry' => $data['bill_country_id'],
            'BillingPostalCode' => $data['bill_postcode'],
            'BillingStreet' => $data['bill_street'],
            'Phone' => $data['bill_telephone'],
            'PersonBirthdate' => $data['dob'],
            'ShippingStreet' => $data['ship_street'],
            'ShippingCity' => $data['ship_city'],
            'ShippingState' => $data['ship_region'],
            'ShippingCountry' => $data['ship_country_id'],
            'ShippingPostalCode' => $data['ship_postcode']
        ];

        if ($data['bill_country_id'] != 'United States') {
            $params['BillingState'] = null;
            $params['BillingCountry'] = null;
        }

        if ($data['ship_country_id'] != 'United States') {
            $params['ShippingState'] = null;
            $params['ShippingCountry'] = null;
        }

        if(!$sfAccountId) {
            $params = ['acct' => $params];
            
            if(isset($params) === true) {
                print_r($params);
            }
            
            $response = $this->createAccount($params);
            
            echo "create account\n";
            print_r($response);
            
			if(isset($response["acctId"]) == true) {
				$id =  $response["acctId"];
			} else {
				$id = null;
			}
        } elseif($sfAccountId != null) {
            $params += ['Id' => $sfAccountId];
            $params = ['acct' => $params];
            $this->updateAccount($params);
        } else {
            echo "Invalid salesforce customer Id.\n";
        }

        return $id;
    }
}
