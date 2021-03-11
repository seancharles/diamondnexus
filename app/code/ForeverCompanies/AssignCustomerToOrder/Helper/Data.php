<?php

namespace ForeverCompanies\AssignCustomerToOrder\Helper;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Data
 * @package ForeverCompanies\AssignCustomerToOrder\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository
    )
    {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
    }

    /**
     * Is Button enabled
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function isEnabled(OrderInterface $order)
    {
        if ($order->getCustomerId() == null) {
            try {
                $customer = $this->customerRepository->get($order->getCustomerEmail());
                if ($customer->getId() !== null) {
                    return true;
                }
            } catch (NoSuchEntityException $e) {
                return false;
            } catch (LocalizedException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * @param $customerId int
     * @param $orderId int
     * @return $this
     */
    public function dispatchCustomerOrderLinkEvent($customerId, $orderId)
    {
        $this->_eventManager->dispatch('ForeverCompanies_guest_to_customer_save', [
            'customer_id' => $customerId,
            'order_id' => $orderId, //incrementId
            'increment_id' => $orderId //$incrementId
        ]);

        return $this;
    }

    /**
     * @param $order OrderInterface
     */
    public function setCustomerData(OrderInterface $order)
    {
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
            $order->setCustomerIsGuest(0);
            $order->setCustomerId($customer->getId());
            $order->setCustomerGroupId($customer->getGroupId());
            $order->setCustomerPrefix($customer->getPrefix());
            $order->setCustomerFirstname($customer->getFirstname());
            $order->setCustomerLastname($customer->getLastname());
            $order->setCustomerMiddlename($customer->getMiddlename());
            $order->setCustomerSuffix($customer->getSuffix());
            $order->setCustomerDob($customer->getDob());
            $order->setCustomerGender($customer->getGender());
            $order->setCustomerTaxvat($customer->getTaxvat());
            $order->setCustomerId($customer->getId());
            $order->setCustomerIsGuest(0);
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
        }

    }
}
