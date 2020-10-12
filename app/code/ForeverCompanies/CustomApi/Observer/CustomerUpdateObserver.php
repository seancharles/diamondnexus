<?php

namespace ForeverCompanies\CustomApi\Observer;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class CustomerUpdateObserver implements ObserverInterface
{
    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * CustomerUpdateObserver constructor.
     * @param Collection $orderCollection
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Collection $orderCollection,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderCollection = $orderCollection;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Customer $customer */
        $customer = $observer->getData('customer_data_object');
        $oldCustomer = $observer->getData('orig_customer_data_object');
        if ($customer->getEmail() !== $oldCustomer->getEmail()) {
            $this->updateOrderEmailsByCustomerId($customer->getEmail(), (int)$customer->getId());
        }
    }

    /**
     * @param string $email
     * @param int $customerId
     */
    protected function updateOrderEmailsByCustomerId(string $email, int $customerId)
    {
        $orders = $this->orderCollection->addAttributeToFilter('customer_id', $customerId)->load();
        /** @var Order $order */
        foreach ($orders->getItems() as $order) {
            $order->setCustomerEmail($email);
            $this->orderRepository->save($order);
        }
    }
}
