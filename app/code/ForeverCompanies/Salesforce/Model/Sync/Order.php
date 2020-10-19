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
    const SALESFORCE_ORDER_ATTRIBUTE_CODE = 'salesforce_order_id';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\Sync\Account
     */
    protected $account;

    /**
     * @var Job
     */
    protected $job;

    /**
     * @var array
     */
    protected $existedOrders = [];

    /**
     * @var array
     */
    protected $createOrderIds = [];

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
     * @param OrderFactory $orderFactory
     * @param Account $account
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
        OrderFactory $orderFactory,
        Account $account,
        DataGetter $dataGetter
    )
    {
        parent::__construct($scopeConfig,
            $resourceConfig,
            $reportFactory,
            $queueFactory,
            $requestLogFactory);
        $this->orderFactory  = $orderFactory;
        $this->data   = $data;
        $this->job = $job;
        $this->dataGetter = $dataGetter;
        $this->account = $account;
    }

    /**
     * Create new a Order in Salesforce
     *
     * @param $increment_id
     * @return string|void
     */
    public function sync($increment_id)
    {
        $model = $this->orderFactory->create()->loadByIncrementId($increment_id);
        $customerId = $model->getCustomerId();
        $date =  date('Y-m-d', strtotime($model->getCreatedAt()));
        $email = $model->getCustomerEmail();
        if ($model->getData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE)){
            return '';
        }

        if ($customerId){
            $accountId = $this->account->sync($customerId);
        } else {
            $accountId = $this->account->syncByEmail($email);
        }

        $params = $this->data->getOrder($model, $this->_type);
        $pricebookId = $this->searchRecords('Pricebook2','Name','Standard Price Book');
        $params += [
                'AccountId' => $accountId,
                'EffectiveDate' => $date,
                'Status' => 'Draft',
                'Pricebook2Id' => $pricebookId,
            ];

        // Create new Order
        $orderId = $this->createRecords($this->_type, $params, $model->getIncrementId());
        $this->saveAttribute($model, $orderId);

        // Add new record to OrderItem need:
        foreach ($model->getAllItems() as $item){
            $productId = $item->getProductId();
            $qty = $item->getQtyOrdered();
            $price  = $item->getPrice() - $item->getDiscountAmount() / $qty;
            if ($price > 0){
                    $pricebookEntryId = $this->searchRecords('PricebookEntry','Product2Id', $productId);
                $output = [
                        'PricebookEntryId' => $pricebookEntryId,
                        'OrderId'          => $orderId,
                        'Quantity'         => $qty,
                        'UnitPrice'        => $price,
                    ];
                $this->createRecords('OrderItem', $output, $productId);
                }
            }

        if ($taxInfo = $this->getTaxItemInfo($model, $orderId)){
                $this->createRecords('OrderItem', $taxInfo, 'TAX');
        }
        if ($shippingInfo = $this->getShippingItemInfo($model, $orderId)){
                $this->createRecords('OrderItem', $shippingInfo, 'SHIPPING');
        }

       return $orderId;

    }

    public function syncAllOrders()
    {
        try {
          $orders = $this->orderFactory->create()->getCollection();
          $lastOrderId = $orders->getLastItem()->getId();
          $count = 0;
          $response = [];
            /** @var \Magento\Sales\Model\Order $order */
          foreach ($orders as $order){
                $this->addRecord($order->getIncrementId());
                $count++;
                if ($count >= 1000 || $order->getId() == $lastOrderId){
                    $response += $this->syncQueue();
                }
          }
          return $response;

        } catch (\Exception $e) {
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Psr\Log\LoggerInterface::class)->debug($e->getMessage());
        }
        return null;
    }

    /**
     * @param string $orderIncrementId
     */
    public function addRecord($orderIncrementId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
        if (!$order->getData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE)){
            $this->addToCreateProductQueue($orderIncrementId);
        }
    }

    public function syncQueue()
    {
        $response = $this->createOrders();
        $this->saveAttributes($this->createOrderIds, $response);
        $response += $this->createOrderItems();
        $this->unsetCreateProductQueue();
        return $response;
    }

    protected function addToCreateProductQueue($orderIncrementId)
    {
        $this->createOrderIds[] = ['mid' => $orderIncrementId];
    }

    protected function unsetCreateProductQueue()
    {
        $this->createOrderIds = [];
    }

    protected function createOrders()
    {
        $params = [];
        $pricebookId = $this->searchRecords('Pricebook2', 'Name', 'Standard Price Book');

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($this->createOrderIds as $id){
            $order = $this->orderFactory->create()->loadByIncrementId($id['mid']);
            $customer = $order->getCustomer();
            $date = date('Y-m-d', strtotime($order->getCreatedAt()));
            $email = $order->getCustomerEmail();

            if ($customer && $customer->getData(Account::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE)){
                $accountId = $customer->getData(Account::SALESFORCE_ACCOUNT_ATTRIBUTE_CODE);
            } elseif($customer && $customer->getId()){
                $accountId = $this->account->sync($customer->getId());
            } else {
                $accountId = $this->account->sync($email);
            }

            $info = $this->data->getOrder($order, $this->_type);
            $info += [
                'EffectiveDate' => $date,
                'Status' => 'Draft',
                'Pricebook2Id' => $pricebookId,
                'AccountId' => $accountId
            ];
            $params[] = $info;
        }
        $response = $this->job->sendBatchRequest('insert', $this->_type,json_encode($params));
        $this->saveReports('create', $this->_type, $response, $this->createOrderIds);
        return $response;
    }

    protected function createOrderItems()
    {
        $params = [];
        $itemIds = [];
        foreach ($this->createOrderIds as $id){
            $order = $this->orderFactory->create()->loadByIncrementId($id['mid']);
            $orderId = $order->getData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE);
            foreach ($order->getAllItems() as $item){
                $qty  = $item->getQtyOrdered();
                $price = $item->getPrice() - $item->getDiscountAmount() / $qty;
              //  $pricebookEntryId = $item->getProduct()->getData(Product::SALESFORCE_PRICEBOOKENTRY_ATTRIBUTE_CODE);
                if ($price > 0){
                    $productId = $item->getProduct()->getData(Product::SALESFORCE_PRODUCT_ATTRIBUTE_CODE);
                   // if (!$productId){
                        //$productId = $this->_product->sync($item->getProductId());
                   // }
                    if ($productId && $orderId){
                        //if (!$pricebookEntryId){
                           // $pricebookEntryId = $this->searchRecords('PricebookEntry', 'Product2Id', $productId);
                       // }
                        $info = [
                           // 'PricebookEntryId' => $pricebookEntryId,
                            'OrderId' => $orderId,
                            'Quantity' => $qty,
                            'UnitPrice' => $price,
                        ];
                        $params[] = $info;
                        $itemIds[] = ['mid' => $item->getProductId()];
                    }
                }
            }
            if ($taxInfo = $this->getTaxItemInfo($order, $orderId)){
                $params[] = $taxInfo;
                $itemIds[] = ['mid' => 'TAX'];
            }
            if ($shippingInfo = $this->getShippingItemInfo($order, $orderId)){
                $params[] = $shippingInfo;
                $itemIds[] = ['mid' => 'SHIPPING'];
            }
        }
        $response = $this->job->sendBatchRequest('insert', 'OrderItem', json_encode($params));
        $this->saveReports('create','OrderItem', $response, $itemIds);
        return $response;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $orderId
     * @return array|null
     */
    protected function getTaxItemInfo($order, $orderId)
    {
        $taxAmount = $order->getTaxAmount();
        if ($taxAmount >0) {
            $info = [
                //'PricebookEntryId' => $this->_scopeConfig->getValue(
                //    XML_TAX_PRICEBOOKENTRY_ID_PATH),
                'OrderId' => $orderId,
                'Quantity' => 1,
                'UnitPrice' => $taxAmount,
            ];
            return $info;
        }
        return null;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param string $orderId
     * @return array|null
     */
    protected function getShippingItemInfo($order, $orderId)
    {
        $shippingAmount = $order->getShippingAmount();
        if ($shippingAmount > 0){
            $info = [
                //'PricebookEntryId' => $this->_scopeConfig->getValue(
                //    Product::XML_SHIPPING_PRICEBOOKENTRY_ID_PATH),
                'OrderId' => $orderId,
                'Quantity' => 1,
                'UnitPrice' => $shippingAmount,
            ];
            return $info;
        }
        return null;
    }

    /**
     * @param $orderIds
     * @param $response
     * @throws \Exception
     */
    protected function saveAttributes($orderIds, $response)
    {
        if (is_array($response) && is_array($orderIds)){
            for($i=0; $i<count($orderIds); $i++){
                $order = $this->orderFactory->create()->loadByIncrementId($orderIds[$i]['mid']);
                if(isset($response[$i]['id']) && $order->getId()){
                    $this->saveAttribute($order, $response[$i]['id']);
                }
            }
        } else {
            throw new \Exception('Response not an array');
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param $salesforceId
     */
    protected function saveAttribute($order, $salesforceId)
    {
        $resource = $order->getResource();
        $order->setData(self::SALESFORCE_ORDER_ATTRIBUTE_CODE, $salesforceId);
        $resource->saveAttribute($order, self::SALESFORCE_ORDER_ATTRIBUTE_CODE);
    }
}
