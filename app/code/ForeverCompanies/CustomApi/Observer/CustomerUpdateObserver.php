<?php

namespace ForeverCompanies\CustomApi\Observer;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerUpdateObserver implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orderCollection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * CustomerUpdateObserver constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
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

    protected function updateOrderEmailsByCustomerId(string $email, int $customerId)
    {
        $orders = $this->orderCollection->addAttributeToFilter('customer_id', $customerId)->load();
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders->getItems() as $order) {
            $order->setCustomerEmail($email);
            $this->orderRepository->save($order);
        }
    }
}
