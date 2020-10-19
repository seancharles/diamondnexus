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
use Magento\Customer\Model\Customer;

class Account extends Connector
{
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'sf_acctid';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var array
     */
    protected $existedAccounts = [];

    /**
     * @var array
     */
    protected $createAccountIds = [];

    /**
     * @var array
     */
    protected $updateAccountIds = [];

    /**
     * @var DataGetter
     */
    protected $dataGetter;

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
     * @param Job $job
     * @param CustomerFactory $orderFactory
     * @param DataGetter $dataGetter
     * @param QueueFactory $queueFactory
     * @param RequestLogFactory $requestLogFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        ReportFactory $reportFactory,
        Data $data,
        Job $job,
        QueueFactory $queueFactory,
        RequestLogFactory $requestLogFactory,
        CustomerFactory $customerFactory,
        DataGetter $dataGetter
    )
    {
        parent::__construct($scopeConfig,
            $resourceConfig,
            $reportFactory,
            $queueFactory,
            $requestLogFactory);
        $this->customerFactory = $customerFactory;
        $this->_type = 'Account';
        $this->data = $data;
        $this->job = $job;
        $this->dataGetter = $dataGetter;
    }

    /**
     * Update or create new a record
     *
     * @param  int     $id
     * @param  boolean $update
     * @return string
     */
    public function sync($id, $update = false)
    {
        $customer = $this->customerFactory->create()->load($id);
        $email =  $customer->getEmail();
        $id  = $this->searchRecords($this->_type, 'Name', $email);
        $name =  $customer->getData('firstname');
        $phone =  $customer->getData('mobilephone');

        if (!$id || ($update && $id)) {
            // Pass data of customer to array
            $data  = $this->data->getCustomer($customer, $this->_type);
            $bill_city = $data['bill_city'] == null ? "" : $data['bill_city'];
            $bill_street = $data['bill_street'] == null ? "" : $data['bill_street'];
            $bill_state = $data['bill_region'] == null ? "" : $data['bill_region'];
            $bill_postalcode = $data['bill_postcode'] == null ? "" : $data['bill_postcode'];
            $bill_countrycode = $data['bill_country_id'] == null ? "" : $data['bill_country_id'];
            $params = [
                'Name' => $name,
                "Web_Account_Id__c" => "786abcMn7",
                "Phone" => $phone,
                "BillingCity" => $bill_city ,
                "BillingStreet" =>  $bill_street ,
                "BillingState" =>     $bill_state,
                "BillingPostalCode" =>  $bill_postalcode,
                "BillingCountryCode" => $bill_countrycode

            ];
            $params = ['acct' => $params];
            if ($update && $id){
                $this->updateAccount($this->_type, $id, $params, $customer->getId());
            } else {
                $response = $this->createAccount($this->_type, $params, $customer->getId());
                $id =  $response["acctId"];
            }
        }
        $this->saveAttribute($customer, $id);
        return $id;
    }

    /**
     * Create new a record by email
     *
     * @param  string $email
     * @return string
     */
    public function syncByEmail($email)
    {
        $id = $this->searchRecords($this->_type, 'Name', $email);
        if (!$id){
            $params = ['Name' => $email];
            $id = $this->createRecords($this->_type, $params);
        }

        return $id;
    }

    /**
     * Sync All Customer on Magento to Salesforce
     */
    public function syncAllAccount()
    {
        try {
           $customers = $this->customerFactory->create()->getCollection();
           $lastCustomerId = $customers->getLastItem()->getId();
           $count = 0;
           $response = [];
            /** @var \Magento\Customer\Model\Customer $customer */
            foreach ($customers as $customer) {
                $this->addRecord($customer->getId());
                $count++;
                if ($count >= 10000 || $customer->getId() == $lastCustomerId){
                    $response += $this->syncQueue();
                }
            }
            return $response;
        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
        return null;
    }

    public function syncQueue()
    {
        $createResponse = $this->createAccounts();
        $this->saveAttributes($this->createAccountIds, $createResponse);
        $updateResponse = $this->updateAccounts();
        $this->saveAttributes($this->updateAccountIds, $updateResponse);
        $response = $createResponse + $updateResponse;
        $this->unsetCreateQueue();
        $this->unsetUpdateQueue();
        return $response;
    }

    /**
     * Send request to create accounts
     */
    protected function createAccounts()
    {
        $response = [];
        if (count($this->createAccountIds) > 0){
            $response = $this->sendAccountsRequest($this->createAccountIds, 'insert');
        }
        return $response;
    }

    /**
     * Send request to update accounts
     */
    protected function updateAccounts()
    {
        $response = [];
        if (count($this->updateAccountIds) > 0) {
            $response = $this->sendAccountsRequest($this->updateAccountIds, 'update');
        }
        return $response;
    }

    /**
     * @param int $customerId
     */
    public function addRecord($customerId)
    {
        $id = $this->checkExistedAccount($customerId);
        if (!$id) {
            $this->addToCreateQueue($customerId);
        } else {
            $this->addToUpdateQueue($id['mid'], $id['sid']);
        }
    }

    protected function addToCreateQueue($customerId)
    {
        $this->createAccountIds[] = ['mid' => $customerId];
    }

    protected function addToUpdateQueue($customerId, $salesforceId)
    {
        $this->updateAccountIds[] = [
            'mid' => $customerId,
            'sid' => $salesforceId
        ];
    }

    protected function unsetCreateQueue()
    {
        $this->createAccountIds = [];
    }

    protected function unsetUpdateQueue()
    {
        $this->updateAccountIds = [];
    }

    protected function sendAccountsRequest($accountsIds, $operation){
        $params = [];
        foreach ($accountsIds as $id){
            $customer = $this->customerFactory->create()->load($id['mid']);
            $info = $this->data->getCustomer($customer, $this->_type);
            $info += [
                'Name' => $customer->getEmail(),
                'AccountNumber' => $customer->getId(),
            ];
            if (isset($id['sid'])){
                $info += ['Id' => $id['sid']];
            }
            $param[] = $info;
        }
        $response = $this->job->sendBatchRequest($operation, $this->_type, json_encode($params));
        $this->saveReports($operation, $this->_type, $response, $accountsIds);
        return $response;
    }

    /**
     * @param int $customerId
     * @return array|bool
     */
    protected function checkExistedAccount($customerId)
    {
        $existedAccounts = $this->getAllSalesforceAccount();
        $customer  = $this->customerFactory->create()->load($customerId);
        foreach ($existedAccounts as $key => $existedAccount){
            if (isset($existedAccount['Name']) && strtolower($customer->getEmail()) == $existedAccount['Name']) {
                return [
                    'mid' => $customer->getId(),
                    'sid' => $existedAccount['Id']
                ];
            }
        }

        return false;
    }

    /**
     * return an array of accounts on Salesforce
     * @return array|mixed|string
     */
    public function getAllSalesforceAccount()
    {
        if (count($this->existedAccounts) > 0 ){
                return $this->existedAccounts;
        }
        $this->existedAccounts = $this->dataGetter->getAllSalesforceOrders();
    }

    /**
     * @param Customer $customer
     * @param String $salesforceId
     * @throws \Exception
     */
    protected function saveAttributes($customerIds, $response)
    {
        if (is_array($response) && is_array($customerIds)){
                for ($i = 0; $i < count($customerIds); $i++){
                    $customer = $this->customerFactory->create()->load($customerIds[$i]['mid']);
                    if (isset($response[$i]['id']) && $customer->getId()){
                        $this->saveAttribute($customer, $response[$i]['id']);
                    }
                }
        } else {
            throw new \Exception('Response not an array');
        }
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
        $customerData->setCustomAttribute(self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE,
            $salesforceId);
        $customer->updateData($customerData);
        /** @var \Magento\Customer\Model\ResourceModel\Customer $customerResource */
        $customerResource = $this->customerFactory->create()->getResource();
        $customerResource->saveAttribute($customer, self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
    }
}
