<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use ForeverCompanies\Salesforce\Model\Connector;
use ForeverCompanies\Salesforce\Model\Data;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Customer\Model\CustomerFactory;

class Account extends Connector
{
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'sf_acctid';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Data
     */
    protected $data;

    /**
     * Order constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param ReportFactory $reportFactory
     * @param Data $data
     * @param CustomerFactory $orderFactory
     * @param RequestLogFactory $requestLogFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        Data $data,
        RequestLogFactory $requestLogFactory,
        \Magento\Config\Model\Config $configModel,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $scopeConfig,
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
     * @param  int     $accountId
     * @param  string  $salesforceId
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

        if(!$sfAccountId) {
            $params = ['acct' => $params];
            $response = $this->createAccount($params);
            $id =  $response["acctId"];
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
