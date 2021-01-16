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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;

class Order extends Connector
{
    const SALESFORCE_ORDER_ATTRIBUTE_CODE = 'sf_orderid';
    const SALESFORCE_ACCOUNT_ATTRIBUTE_CODE = 'sf_acctid';
    const SALESFORCE_ORDER_ATTRIBUTE_CODE_ITEM_ID = 'sf_order_itemid';

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

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
     * @param Data $data
     * @param RequestLogFactory $requestLogFactory
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        Data $data,
        RequestLogFactory $requestLogFactory,
        \Magento\Config\Model\Config $configModel,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $scopeConfig,
            $resourceConfig,
            $requestLogFactory,
            $configModel
        );
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->data   = $data;
        $this->_type = 'Order';
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
    public function sync($increment_id, $sfOrderId, $sfAccountId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($increment_id);
        
        $params = $this->data->getOrder($order, $this->_type);
        
        $date = date('Y-m-d', time());
        
        $data = [
            'Web_Order_Id__c' => $params['entity_id'],
            'Web_Order_Number__c' => $params['increment_id'],
            'Store_Name__c' => $params['store_name'],
            'EffectiveDate' => $date,
            'Status' => 'Draft',
            
            'First_Name__c' => $params['customer_firstname'],
            'Last_Name__c' => $params['customer_lastname'],
            'Email' => $params['customer_email'],
            'Phone__c' => $params['bill_telephone'],
            
            'BillingStreet' => $params['bill_street'],
            'BillingCity' => $params['bill_city'],
            'BillingState' => $params['bill_region'],
            'BillingPostalCode' => $params['bill_postcode'],
            'BillingCountryCode' => $params['bill_country_id'],
            
            'Order_Subtotal__c' => $params['subtotal'],
            'Discount_Amount__c' => $params['discount_amount'],
            'Order_Total__c' => $params['grand_total'],
            'Order_Status__c' => $params['status'],
            'Ship_Method__c' => $params['shipping_method'],
            'Tax_Amount__c' => $params['tax_amount']
        ];

        // todo: add handling for guest order updates (will need to pull customer by email)
        //
        if ($sfOrderId) {
            echo "Update Order: " . $order->getIncrementId() . "\n";
            $data += ['Id' => $sfOrderId];
            $result = ['order' => $data];
            $this->updateOrder($result);
            
        } elseif($sfAccountId != null) {
            echo "Create Order: " . $order->getIncrementId() . "\n";
            $data += ['AccountId' => $sfAccountId];
            $result = ['order' => $data];
            return $this->createOrder($result);
            
        } else {
            echo "Create Guest Order: " . $order->getIncrementId() . "\n";
            if(!$order->getCustomerId()) {
                $result = ['order' => $data];
                return $this->createGuestOrder($result);
            }
        }

        return false;
    }
    
    public function syncLineItems($orderId, $lastsyncAt)
    {
        // load order
        $order = $this->orderRepository->get($orderId);
        
        // get sf order id
        $sfOrderId = $order->getData('sf_orderid');
        
        $orderItems = $order->getAllItems();
        
        if($sfOrderId) {
            $this->clearOrderLines($sfOrderId);
            
            foreach($orderItems as $item) {
                
                $sfItemId = $item->getData('sf_order_itemid');
                
                $data = [
                    'Order__c' => $sfOrderId,
                    'Web_Product_Id__c' => $item->getSku(),
                    'Amount__c' => $item->getPrice(),
                    'Name' => $item->getName()
                ];
                
                if($sfItemId) {
                    $data['Id'] = $sfItemId;
                    $result = ['line' => $data];
                    $this->updateOrderLine($result);
                } else {
                    $result = ['line' => $data];
                    $sfItemId = $this->createOrderLine($result);
                }
                
                if($sfItemId) {
                    $item->setData('sf_order_itemid', $sfItemId)->save();
                    $item->setData('lastsync_at', $lastsyncAt)->save();
                }
                
            }
        } else {
            echo "Unable to sync items order id missing.";
        }
    }
}
