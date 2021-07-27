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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\User\Model\UserFactory;
use Magento\Directory\Model\RegionFactory;
use ShipperHQ\Shipper\Model\ResourceModel\Order\Detail;
use Magento\Framework\Exception\LocalizedException;

class Order extends Connector
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;
    
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var Account
     */
    protected $account;
    protected $userFactory;
    protected $shipperDetailResourceModel;

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
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderFactory $orderFactory
     * @param CustomerFactory $customerFactory
     * @param UserFactory $userFactory
     * @param RegionFactory $regionFactory
     * @param Detail $shipperDetailResourceModel
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
		TypeListInterface $cacheTypeList,
        ResourceModelConfig $resourceConfig,
        Data $data,
        RequestLogFactory $requestLogFactory,
        Config $configModel,
        OrderRepositoryInterface $orderRepository,
        OrderFactory $orderFactory,
        CustomerFactory $customerFactory,
        UserFactory $userFactory,
        RegionFactory $regionFactory,
        Detail $shipperDetailResourceModel
    ) {
        parent::__construct(
            $scopeConfig,
			$configWriter,
			$cacheTypeList,
            $resourceConfig,
            $requestLogFactory,
            $configModel
        );
        $this->orderFactory = $orderFactory;
        $this->orderRepository = $orderRepository;
        $this->customerFactory = $customerFactory;
        $this->userFactory = $userFactory;
        $this->regionFactory = $regionFactory;
        $this->shipperDetailResourceModel = $shipperDetailResourceModel;
        $this->data   = $data;
        $this->_type = 'Order';
    }

    /**
     * Create or Update an Order in Salesforce
     *
     * @param $increment_id
     * @param $sfOrderId
     * @param $sfAccountId
     * @return string|void
     */
    public function sync($increment_id, $sfOrderId, $sfAccountId)
    {
        $order = $this->orderFactory->create()->loadByIncrementId($increment_id);
        $params = $this->data->getOrder($order, $this->_type);
        $salesPersonId = $params['sales_person_id'];
        $shipDate = null;

        if ($salesPersonId > 0) {
            $user = $this->userFactory->create()->load($salesPersonId);
            $salesRep = strtolower($user->getFirstName() . "." . $user->getLastName());
        } else {
            $salesRep = "Web";
        }

        $connection = $this->shipperDetailResourceModel->getConnection();
        $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
            ->where('order_id = ?', $order->getEntityId())
            ->order('id desc')
            ->limit(1);
        $shipperData = $connection->fetchRow($select);

        if(isset($shipperData['dispatch_date']) === true) {
            $shipDate = $shipperData['dispatch_date'];
        }

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

            'Order_Subtotal__c' => $params['subtotal'],
            'Discount_Amount__c' => $params['discount_amount'],
            'Order_Total__c' => $params['grand_total'],
            'Order_Status__c' => $params['status'],
            'Ship_Method__c' => $params['shipping_method'],
            'Tax_Amount__c' => $params['tax_amount'],
            'Sales_Rep__c' => $salesRep,
            'Ship_Date__c' => $shipDate
        ];

        if($params['bill_country_id'] == "US") {
            $data['Billing_City__c'] = $params['bill_city'];
            $data['Billing_State__c'] = $params['bill_region'];
            $data['Billing_Country__c'] = $params['bill_country_id'];
            $data['Billing_Postal_Code__c'] = $params['bill_postcode'];
            $data['Billing_Street__c'] = $params['bill_street'];
        }

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
    
    public function syncLineItems($orderId, $sfOrderId)
    {
        // load order
        $order = $this->orderRepository->get($orderId);
        
        $orderItems = $order->getAllItems();
        
        if($sfOrderId) {
            $this->clearOrderLines(['id' =>$sfOrderId]);
            
            foreach($orderItems as $item) {
                $data = [
                    'Order__c' => $sfOrderId,
                    'Web_Product_Id__c' => $item->getSku(),
                    'Amount__c' => $item->getPrice(),
                    'Name' => $item->getName()
                ];

                $lineId = $this->createOrderLine(['line' => $data]);

                if($lineId === false) {
                    echo "Unable to add order item " . $item->getSku();
                }
            }
        } else {
            echo "Unable to sync items order id missing.";
        }
    }
}
