<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_EditOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Model\Order;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtension;
use Magento\Customer\Api\Data\CustomerExtensionInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Customer;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Info;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address as AddressModel;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order as ResourceOrder;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Mageplaza\EditOrder\Model\Order\Total as OrderTotal;
use Psr\Log\LoggerInterface;

/**
 * Class Edit
 * @package Mageplaza\EditOrder\Model\Order
 */
class Edit
{
    /**
     * @var Info
     */
    protected $info;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var OrderAddressInterface
     */
    protected $orderAddressInterface;

    /**
     * @var ResourceOrder
     */
    protected $orderResourceModel;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepoInterface;

    /**
     * @var AccountManagement
     */
    protected $accountManagement;

    /**
     * @var CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var CustomerInterface
     */
    protected $customerFactory;

    /**
     * @var Customer
     */
    protected $customerModel;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var CustomerExtensionInterfaceFactory
     */
    private $extensionFactory;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Edit constructor.
     *
     * @param Info $info
     * @param RegionFactory $regionFactory
     * @param OrderAddressInterface $orderAddressInterface
     * @param CollectionFactory $orderCollectionFactory
     * @param ResourceOrder $orderResourceModel
     * @param CustomerRepositoryInterface $customerRepoInterface
     * @param AccountManagement $accountManagement
     * @param CustomerInterface $customerFactory
     * @param Customer $customerModel
     * @param OrderFactory $orderFactory
     * @param CustomerExtensionInterfaceFactory $extensionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Info $info,
        RegionFactory $regionFactory,
        OrderAddressInterface $orderAddressInterface,
        CollectionFactory $orderCollectionFactory,
        ResourceOrder $orderResourceModel,
        CustomerRepositoryInterface $customerRepoInterface,
        AccountManagement $accountManagement,
        CustomerInterface $customerFactory,
        Customer $customerModel,
        OrderFactory $orderFactory,
        CustomerExtensionInterfaceFactory $extensionFactory,
        LoggerInterface $logger
    ) {
        $this->info = $info;
        $this->regionFactory = $regionFactory;
        $this->orderAddressInterface = $orderAddressInterface;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderResourceModel = $orderResourceModel;
        $this->_customerRepoInterface = $customerRepoInterface;
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->customerModel = $customerModel;
        $this->orderFactory = $orderFactory;
        $this->extensionFactory = $extensionFactory;
        $this->_logger = $logger;
    }

    /**
     * @param Quote $quote
     *
     * @return array
     */
    public function getQuoteItemsData($quote)
    {
        $discountAmount = 0;
        $taxAmount = 0;
        $quoteItems = $quote->getAllItems();

        /** @var Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $taxAmount += $quoteItem->getBaseTaxAmount();
            $discountAmount += $quoteItem->getBaseDiscountAmount();
        }

        return [
            'type'            => OrderTotal::TYPE_COLLECT_ITEMS,
            'tax_amount'      => $taxAmount,
            'discount_amount' => $discountAmount
        ];
    }

    /**
     * @param int $orderId
     *
     * @param array $data
     *
     * @return array
     */
    public function setAddress($orderId, $data)
    {
        $result = [];
        $addressId = $data['address_id'];
        /** @var $address OrderAddressInterface|AddressModel */
        $address = $this->orderAddressInterface->load($addressId);
        $data = $this->updateRegionData($data);

        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $address->save();
                $order = $this->orderFactory->create()->load($orderId);
                if ($address->getAddressType() === 'shipping') {
                    $address = $order->getShippingAddress();
                } elseif ($address->getAddressType() === 'billing') {
                    $address = $order->getBillingAddress();
                }

                $addressHtml = $this->info->getFormattedAddress($address);
                $result = [
                    'success' => $addressHtml,
                ];
            } catch (Exception $e) {
                $result = [
                    'error'   => true,
                    'message' => $e->getMessage()
                ];
            }
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return array
     */
    public function setInfoData($order, $data)
    {
        $incrementId = $data['increment'];

        if ($this->checkIncrementId($incrementId, $order->getIncrementId())) {
            return [
                'error'   => true,
                'message' => __('The increment id already exists')
            ];
        
        }

        $order->setIncrementId($data['increment']);
        $order->setCreatedAt($data['date']);
        $order->setStatus($data['status']);

        try {
            $this->orderResourceModel->save($order);
            $result = ['success' => true];
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return array
     * @throws LocalizedException
     */
    public function setCustomerData($order, $data)
    {
        $email = $data['email'];

        /** update data customer in order */
        $order->setCustomerEmail($email);
        $order->setCustomerPrefix($data['name-prefix']);
        $order->setCustomerFirstname($data['first-name']);
        $order->setCustomerMiddlename($data['middle-name']);
        $order->setCustomerLastname($data['last-name']);
        $order->setCustomerSuffix($data['name-suffix']);
        $order->setCustomerGroupId($data['customer-group']);
        $order->setCustomerDob($data['dob']);
        $order->setCustomerTaxvat($data['taxvat']);
        $order->setCustomerGender($data['gender']);

        /** create/update data for customer info */
        if (isset($data['save-customer'])) {
            if ($this->checkExistCustomer($email)) {
                try {
                    $customer = $this->_customerRepoInterface->get($email);
                    $this->setData($customer, $data, $email);
                    $this->_customerRepoInterface->save($customer); // update customer

                    $order->setCustomerIsGuest(0); //set info if guest
                    $order->setCustomerId($customer->getId());
                } catch (NoSuchEntityException $e) {
                    $this->_logger->critical($e->getMessage());
                } catch (LocalizedException $e) {
                    $this->_logger->critical($e->getMessage());
                }
            } else {
                $customer = $this->customerFactory;
                $this->setData($customer, $data, $email);
                $this->accountManagement->createAccount($customer);  // create customer
            }
        }

        try {
            $this->orderResourceModel->save($order);
            $result = ['success' => true];
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * @param CustomerInterface $customer
     *
     * @param array $data
     *
     * @param string $email
     */
    public function setData($customer, $data, $email)
    {
        $customer->setWebsiteId($data['website-id']);
        $customer->setEmail($email);
        $customer->setPrefix($data['name-prefix']);
        $customer->setFirstname($data['first-name']);
        $customer->setMiddlename($data['middle-name']);
        $customer->setLastname($data['last-name']);
        $customer->setSuffix($data['name-suffix']);
        $customer->setGroupId($data['customer-group']);
        $customer->setDob($data['dob']);
        $customer->setTaxvat($data['taxvat']);
        $customer->setGender($data['gender']);

        if ($data['vertex']) {
            $extensionAttributes = $this->getExtensionAttributes($customer);
            $extensionAttributes->setVertexCustomerCode($data['vertex']);
        }
    }

    /**
     * @param CustomerInterface $customer
     *
     * @return CustomerExtension|CustomerExtensionInterface
     */
    private function getExtensionAttributes(CustomerInterface $customer)
    {
        $extensionAttributes = $customer->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
            $customer->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Check email already exist
     *
     * @param $email
     *
     * @return int||null
     */
    public function checkExistCustomer($email)
    {
        $customerData = $this->customerModel->getCollection()->addFieldToFilter('email', $email);

        return $customerData->getFirstItem()->getId();
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     *
     * @return array
     */
    private function updateRegionData($attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        return $attributeValues;
    }

    /**
     * Check the existence of increment id
     *
     * @param int $newId
     * @param int $currentId
     *
     * @return bool
     */
    public function checkIncrementId($newId, $currentId)
    {
        if ($newId === $currentId) {
            return false;
        }
        $count = $this->_orderCollectionFactory->create()->addFieldToFilter('increment_id', $newId)->getSize();

        return (bool) $count;
    }
}
