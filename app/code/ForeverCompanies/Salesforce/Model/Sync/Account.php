<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use ForeverCompanies\Salesforce\Model\ReportFactory as ReportFactory;
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
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        Data $data,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $scopeConfig,
            $resourceConfig,
            $reportFactory,
            $queueFactory,
            $requestLogFactory
        );
        $this->customerFactory = $customerFactory;
        $this->_type = 'Account';
        $this->data = $data;
    }

    /**
     * Create or update new Account in Salesforce
     *
     * @param  int     $id
     * @param  string  $salesforceId
     * @return string
     */
    public function sync($id)
    {
		$customer = $this->customerFactory->create()->load($id);
		
		echo "" . $customer->getData('sf_acctid') . "\n";
		echo "" . $customer->getData('lastsync_at') . "\n";
		
		//print_r($customer->toArray());
		//exit;
		
		$salesforceId = $customer->getData(self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
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

		echo "salesforceId = " . $salesforceId . "\n";

        if (!$salesforceId) {

            $params = ['acct' => $params];
            $response = $this->createAccount($this->_type, $params, $customer->getId());
            $id =  $response["acctId"];

        } elseif ($salesforceId) {

            $params += ['Id' => $salesforceId];
            $params = ['acct' => $params];
            $this->updateAccount($this->_type, $salesforceId, $params, $customer->getId());
        }

        return $id;
    }


    /**
     * @param Customer $customer
     * @param String $salesforceId
     * @throws \Exception
     */
    protected function saveAttribute($customer, $salesforceId)
    {
        $customerData = $customer->getDataModel();
        $customerData->setId($customer->getId());
        $customerData->setCustomAttribute(
            self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE,
            $salesforceId
        );
        $customer->updateData($customerData);
        /** @var \Magento\Customer\Model\ResourceModel\Customer $customerResource */
        $customerResource = $this->customerFactory->create()->getResource();
        $customerResource->saveAttribute($customer, self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
    }
}
