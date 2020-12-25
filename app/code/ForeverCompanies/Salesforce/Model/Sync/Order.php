<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Sync;

use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\RequestLogFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use ForeverCompanies\Salesforce\Model\ReportFactory as ReportFactory;
use ForeverCompanies\Salesforce\Model\Connector;
use ForeverCompanies\Salesforce\Model\Data;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\CustomerFactory;

class Order extends Connector
{
    const SALESFORCE_ORDER_ATTRIBUTE_CODE = 'sf_orderid';
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'sf_acctid';
    const SALESFORCE_ORDER_ATTRIBUTE_CODE_ITEM_ID = 'sf_order_itemid';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\Sync\Account
     */
    protected $account;

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
     * @param OrderFactory $orderFactory
     * @param Account $account
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
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        Account $account
    ) {
        parent::__construct(
            $scopeConfig,
            $resourceConfig,
            $reportFactory,
            $queueFactory,
            $requestLogFactory
        );
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->data   = $data;
        $this->_type = 'Order';
        $this->account = $account;
    }

    /**
     * Create or Update an Order in Salesforce
     *
     * @param $salesforceCustomerId
     * @param $increment_id
     * @param $guest
     * @param $salesforceOrderId
     * @return string|void
     */
    public function sync($increment_id)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($increment_id);
        $salesforceOrderId = $order->getData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE);
        
        echo "salesforceOrderId = " . $salesforceOrderId . "\n";
        
        // get the customer
        if ($order->getCustomerId()) {
            $customer = $this->customerFactory->create()->load($order->getCustomerId());
            $salesforceCustomerId = $customer->getData(self::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
        } else {
            $salesforceCustomerId = null;
        }
        
        echo "salesforceCustomerId = " . $salesforceCustomerId . "\n";
        
        $params = $this->data->getOrder($order, $this->_type);
        $date = date('Y-m-d', time());
        $data = [
            'Web_Order_Id__c' => $params['entity_id'],
            'EffectiveDate' => $date,
            'Status' => 'Draft',
            'BillingStreet' => $params['bill_street'],
            'BillingCity' => $params['bill_city'],
            'BillingState' => $params['bill_region'],
            'BillingPostalCode' => '53132',//$params['bill_postalcode'],
            'ShippingStreet' => $params['ship_street'],
            'ShippingCity' => $params['ship_city'],
            'ShippingState' => $params['ship_region'],
            'ShippingPostalCode' => '53132',//$params['ship_postalcode'],
            'Order_Subtotal__c' => '100',//$params['subtotal'],
            'Discount_Amount__c' => '10',//$params['discount_amount'],
            'Order_Total__c' => '110',//$params['grand_total'],
            'Order_Status__c' => 'processing',//$params['status'],
            'Ship_Method__c' => 'Ground',//$params['shipping_description'],
            'Store_Name__c' => 'Diamond Nexus',//$params['store_name'],
            'Web_Order_Number__c' => '1000000001',//$params['increment_id'],
            'Tax_Amount__c' => '10',//$params['tax_amount']
        ];

        // Create new Order
        if ($salesforceOrderId) {
            $data += ['Id' => $salesforceOrderId];
            $result = ['order' => $data];
            $this->updateOrder($this->_type, $result, $order->getIncrementId());
        } elseif ($salesforceCustomerId) {
            $data += ['AccountId' => $salesforceCustomerId];
            $result = ['order' => $data];
            return $this->createOrder($this->_type, $result, $order->getIncrementId());
        } else {
            if (!$order->getCustomerId()) {
                return $this->createGuestOrder($this->_type, $params, $order->getIncrementId());
            }
        }

        return false;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $salesforceId
     * @throws \Exception
     */
    protected function saveOrderAttribute($order, $orderId)
    {
        if ($orderId) {
            $order->setData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE, $orderId);
            $order->save();
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $salesforceId
     * @throws \Exception
     */
    protected function saveOrderItemIdAttribute($order, $orderItemId)
    {
        if ($orderItemId) {
            $order->setData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE_ITEM_ID, $orderItemId);
            $order->save();
        }
    }
}
