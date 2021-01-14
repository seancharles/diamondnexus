<?php

namespace ForeverCompanies\CustomAdmin\Block\Adminhtml;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Split extends \Magento\Backend\Block\Template
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CustomerInterface
     */
    protected $customer;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    protected $rmaCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $orderCollectionFactory,
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $customerId = $this->getRequest()->getParam('customer_id');
        try {
            $this->customer = $this->customerRepository->getById($customerId);
        } catch (NoSuchEntityException $e) {
            $this->_logger->critical('Customer ID = ' . $customerId . ' not found');
        } catch (LocalizedException $e) {
            $this->_logger->critical('Customer ID = ' . $customerId . ' not found');
        }
    }

    /**
     * @return string
     */
    public function getSplitUrl()
    {
        return $this->getUrl('customadmin/split/split');
    }

    /**
     * @return array
     */
    public function getAddresses()
    {
        $addresses = [];
        foreach ($this->customer->getAddresses() as $address) {
            $postcode = $address->getPostcode();
            $city = $address->getCity();
            $street = $address->getStreet()[0];
            $state = $address->getRegion()->getRegion();
            $phone = $address->getTelephone();
            $addresses[$address->getId()] = $street . ' ' . $city . ' ' . $state . ' ' . $postcode . ' ' . $phone;
        }
        return $addresses;
    }

    /**
     * @return array
     */
    public function getOrders()
    {
        $ordersData = [];
        $orderCollection = $this->orderCollectionFactory->create();
        $orders = $orderCollection->addAttributeToFilter('customer_id', $this->customer->getId())->load();
        foreach ($orders->getItems() as $item) {
            $incrementId = $item->getIncrementId();
            $shippingDescription = $item->getShippingDescription();
            $status = $item->getStatus();
            $grandTotal = $item->getGrandTotal();
            $ordersData[$item->getId()] = $incrementId . ' ' . $shippingDescription . ' ' . $status . ' ' . $grandTotal;
        }
        return $ordersData;
    }

    /**
     * @return array
     */
    public function getReturns()
    {
        $returnsData = [];
        $fromRmaCollection = $this->rmaCollectionFactory->create();
        $fromRma = $fromRmaCollection->addFieldToFilter('customer_id', $this->customer->getId())->load();
        if ($fromRma->count() > 0) {
            foreach ($fromRma->getItems() as $rmaId => $rma) {
                $orderIncrementId = $rma->getOrderIncrementId();
                $dateRequested = $rma->getDateRequested();
                $status = $rma->getStatus();
                $email = $rma->getCustomerCustomEmail();
                $returnsData[$rmaId] = $orderIncrementId . ' ' . $dateRequested . ' ' . $status . ' ' . $email;
            }
        }
        return $returnsData;
    }
}
