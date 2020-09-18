<?php

namespace ForeverCompanies\CustomApi\Observer\Sales;

use ForeverCompanies\CustomApi\Model\ExtSalesOrderUpdate;
use ForeverCompanies\CustomApi\Model\ExtSalesOrderUpdateFactory;
use ForeverCompanies\CustomApi\Model\ResourceModel\ExtSalesOrderUpdate as ExtResource;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

/**
 * Save original order status.
 */
class OrderSaveBefore implements ObserverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var ExtSalesOrderUpdateFactory
     */
    protected $extSalesOrderUpdateFactory;

    /**
     * @var ExtResource
     */
    protected $extResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $checkingFields = [
        'status',
        'shipping_address_id',
        'anticipated_shipdate',
        'delivery_date',
        'status_histories'
    ];

    /**
     * OrderSaveBefore constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param ExtSalesOrderUpdateFactory $extSalesOrderUpdateFactory
     * @param ExtResource $extResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ExtSalesOrderUpdateFactory $extSalesOrderUpdateFactory,
        ExtResource $extResource,
        LoggerInterface $logger
    )
    {
        $this->orderRepository = $orderRepository;
        $this->extSalesOrderUpdateFactory = $extSalesOrderUpdateFactory;
        $this->extResource = $extResource;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     * a. Order Status
     * b. Shipping Address
     * c. Anticipated Ship Date
     * d. Delivery Date
     * e. Order Notes/Comment
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getOrder();
        //order is new
        if (!$order->getId()) {
            return $this;
        }
        /** @var Order $oldOrderData */
        $oldOrderData = $this->orderRepository->get($order->getId())->getData();
        $newOrderData = $order->getData();
        $changes = [];
        foreach ($this->checkingFields as $key) {
            if ($oldOrderData[$key] != $newOrderData[$key]) {
                $changes[] = $key;
            }
        }
        if (count($changes > 0)) {
            $extOrder = $this->extSalesOrderUpdateFactory->create();
            $changesText = implode(', ', $changes);
            $extOrder->setOrderId($order->getId());
            $extOrder->setUpdatedFields($changesText);
            $extOrder->setFlag(0);
            try {
                $this->extResource->save($extOrder);
            } catch (AlreadyExistsException $e) {
                $this->logger->error('Can\'t create new ExtOrder - ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->logger->error('Something went wrong when order updates - ' . $e->getMessage());
            }
        }
        return $this;
    }
}
