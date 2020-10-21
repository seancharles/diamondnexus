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

class Order extends Connector
{
    const SALESFORCE_ORDER_ATTRIBUTE_CODE = 'sf_orderid';
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
        Account $account
    ) {
        parent::__construct(
            $scopeConfig,
            $resourceConfig,
            $reportFactory,
            $queueFactory,
            $requestLogFactory
        );
        $this->orderFactory  = $orderFactory;
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
    public function sync($salesforceCustomerId, $increment_id, $guest, $salesforceOrderId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($increment_id);
        $params = $this->data->getOrder($order, $this->_type);
        $date = date('Y-m-d', time());
        $data = [
            'Web_Order_Id__c' => $params['entity_id'],
            'EffectiveDate' => $date,
            'Status' => 'Draft',
            'BillingStreet' => $params['bill_street'],
            'BillingCity' => $params['bill_city'],
            'BillingState' => $params['bill_region'],
            'BillingPostalCode' => $params['bill_postalcode'],
            'ShippingStreet' => $params['ship_street'],
            'ShippingCity' => $params['ship_city'],
            'ShippingState' => $params['ship_region'],
            'ShippingPostalCode' => $params['ship_postalcode'],
            'Order_Subtotal__c' => $params['subtotal'],
            'Discount_Amount__c' => $params['discount_amount'],
            'Order_Total__c' => $params['grand_total'],
            'Order_Status__c' => $params['status'],
            'Ship_Method__c' => $params['shipping_description'],
            'Store_Name__c' => $params['store_name'],
            'Web_Order_Number__c' => $params['increment_id'],
            'Tax_Amount__c' => $params['tax_amount']
        ];

        // Create new Order
        if ($guest) {
            $orderItemId = $this->createGuestOrder($this->_type, $params, $order->getIncrementId());
            $this->saveOrderItemIdAttribute($order, $orderItemId);
        } else {
            if ($salesforceOrderId) {
                $data += ['Id' => $salesforceOrderId];
                $result = ['order' => $data];
                $this->updateOrder($this->_type, $result, $order->getIncrementId());
            } else {
                $data += ['AccountId' => $salesforceCustomerId];
                $result = ['order' => $data];
                $orderId = $this->createOrder($this->_type, $result, $order->getIncrementId());
                $this->saveOrderAttribute($order, $orderId);
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
