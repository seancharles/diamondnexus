<?php

declare(strict_types=1);

namespace ForeverCompanies\CustomAdmin\Helper;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Model\ResourceModel\Customer;
use Magento\CustomerBalance\Model\BalanceFactory;
use Magento\CustomerBalance\Model\ResourceModel\Balance;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Stdlib\DateTime;
use Magento\GiftRegistry\Model\EntityFactory;
use Magento\GiftRegistry\Model\ResourceModel\Entity;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Paypal\Model\Billing\Agreement;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Quote\Model\QuoteRepository;
use Magento\Reward\Model\ResourceModel\Reward;
use Magento\Reward\Model\RewardFactory;
use Magento\Rma\Model\RmaRepository;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Wishlist\Model\Wishlist;

class Data extends AbstractHelper
{
    /**
     * @var ToOrderItem
     */
    protected $quoteToOrder;

    /**
     * @var QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Customer
     */
    protected $customerResource;

    /**
     * @var \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory
     */
    protected $rmaCollectionFactory;

    /**
     * @var RmaRepository
     */
    protected $rmaRepository;

    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @var \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory
     */
    protected $billingAgreementCollectionFactory;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var Subscriber
     */
    protected $subscriberResource;

    /**
     * @var Entity
     */
    protected $giftResourceModel;

    /**
     * @var EntityFactory
     */
    protected $giftFactory;

    /**
     * @var Balance
     */
    protected $balanceResource;

    /**
     * @var BalanceFactory
     */
    protected $balanceFactory;

    /**
     * @var RewardFactory
     */
    protected $rewardFactory;

    /**
     * @var Reward
     */
    protected $rewardResource;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerFactory;

    /**
     * @var AddressRepository
     */
    protected $addressRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param ToOrderItem $quoteToOrder
     * @param Wishlist $wishlist
     * @param Subscriber $subscriberResource
     * @param Entity $giftResourceModel
     * @param Balance $balanceResource
     * @param Reward $rewardResource
     * @param Customer $customerResource
     * @param OrderRepository $orderRepository
     * @param QuoteRepository $quoteRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param RmaRepository $rmaRepository
     * @param AddressRepository $addressRepository
     * @param SubscriberFactory $subscriberFactory
     * @param EntityFactory $giftFactory
     * @param BalanceFactory $balanceFactory
     * @param RewardFactory $rewardFactory
     * @param CustomerInterfaceFactory $customerFactory
     * @param CollectionFactory $orderCollectionFactory
     * @param \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory
     * @param \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $billingAgreementCollectionFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Context $context,
        ToOrderItem $quoteToOrder,
        Wishlist $wishlist,
        Subscriber $subscriberResource,
        Entity $giftResourceModel,
        Balance $balanceResource,
        Reward $rewardResource,
        Customer $customerResource,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        CustomerRepositoryInterface $customerRepository,
        RmaRepository $rmaRepository,
        AddressRepository $addressRepository,
        SubscriberFactory $subscriberFactory,
        EntityFactory $giftFactory,
        BalanceFactory $balanceFactory,
        RewardFactory $rewardFactory,
        CustomerInterfaceFactory $customerFactory,
        CollectionFactory $orderCollectionFactory,
        \Magento\Rma\Model\ResourceModel\Rma\CollectionFactory $rmaCollectionFactory,
        \Magento\Paypal\Model\ResourceModel\Billing\Agreement\CollectionFactory $billingAgreementCollectionFactory,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->quoteToOrder = $quoteToOrder;
        $this->wishlist = $wishlist;
        $this->subscriberResource = $subscriberResource;
        $this->subscriberFactory = $subscriberFactory;
        $this->giftFactory = $giftFactory;
        $this->giftResourceModel = $giftResourceModel;
        $this->balanceResource = $balanceResource;
        $this->balanceFactory = $balanceFactory;
        $this->rewardFactory = $rewardFactory;
        $this->rewardResource = $rewardResource;
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->customerResource = $customerResource;
        $this->customerFactory = $customerFactory;
        $this->rmaRepository = $rmaRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->rmaCollectionFactory = $rmaCollectionFactory;
        $this->billingAgreementCollectionFactory = $billingAgreementCollectionFactory;
        $this->dateTime = $dateTime;
    }

    /**
     * @param $fromCustomerId
     * @param $toCustomerId
     * @throws NoSuchEntityException
     * @throws LocalizedException
     * @throws Exception
     */
    public function mergeCustomer($fromCustomerId, $toCustomerId)
    {
        $toCustomer = $this->customerRepository->getById($toCustomerId);
        $fromCustomer = $this->customerRepository->getById($fromCustomerId);
        /** 1. Find all orders for Customer 2 and assign them to Customer 1. */
        $orderCollection = $this->orderCollectionFactory->create();
        $orders = $orderCollection->addAttributeToFilter('customer_id', $fromCustomerId)->load();
        if ($orders->getItems() !== null) {
            /** @var Order $order */
            foreach ($orders->getItems() as $order) {
                $this->changeOrder($order, $toCustomer);
            }
        }
        /**  2. Bring all addresses from Customer 2 to Customer 1 (leave Customer 1’s default addresses alone) */
        if ($fromCustomer->getAddresses() !== null) {
            $toAddresses = $toCustomer->getAddresses();
            foreach ($fromCustomer->getAddresses() as $address) {
                $toAddresses[] = $address;
            }
            $toCustomer->setAddresses($toAddresses);
        }
        /** 3. Move all returns for Customer 2 to Customer 1. */
        $fromRma = $this->rmaCollectionFactory->create()->addFieldToFilter('customer_id', $fromCustomerId)->load();
        if ($fromRma->count() > 0) {
            foreach ($fromRma->getAllIds() as $rmaId) {
                $rma = $this->rmaRepository->get($rmaId);
                $rma->setCustomerId($toCustomerId);
                if ($rma->getCustomerCustomEmail() !== null) {
                    $rma->setCustomerCustomEmail($toCustomer->getEmail());
                }
                $this->rmaRepository->save($rma);
            }
        }
        /** 4. Move items in Customer 2’s shopping cart to Customer 1’s cart. */
        try {
            if ($this->quoteRepository->getActiveForCustomer($fromCustomerId)) {
                $fromCart = $this->quoteRepository->getForCustomer($fromCustomerId);
                $itemsFromCart = $fromCart->getAllItems();
                if (count($itemsFromCart) > 0) {
                    $toCart = $this->quoteRepository->getForCustomer($toCustomerId);
                    foreach ($itemsFromCart as $itemFromCart) {
                        $toCart->addItem($itemFromCart);
                    }
                    $this->quoteRepository->save($toCart);
                    $this->quoteRepository->delete($fromCart);
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->info('Customer ID = ' . $fromCustomerId . 'don\'t have actual quote (active cart)');
        }

        /** 5. Move Customer 2’s wish lists and items to Customer 1. */
        $wishlistItems = $this->wishlist->loadByCustomerId($fromCustomerId)->getItemCollection();
        if ($wishlistItems->count() > 0) {
            $toWishlist = $this->wishlist->loadByCustomerId($toCustomerId)->getItemCollection();
            foreach ($wishlistItems->getItems() as $wishlistItem) {
                $toWishlist->addItem($wishlistItem);
            }
            $toWishlist->save();
        }
        /** 6. Move Customer 2’s billing agreements information to Customer 1. */
        $billingAgreementCollection = $this->billingAgreementCollectionFactory->create();
        $fromCustomerBilling = $billingAgreementCollection->addFieldToFilter('customer_id', $fromCustomerId);
        if ($fromCustomerBilling->count() > 0) {
            /** @var Agreement $fromBilling */
            foreach ($fromCustomerBilling->getItems() as $fromBilling) {
                $fromBilling->setData('customer_id', $toCustomerId);
                $fromCustomerBilling->getResource()->save($fromBilling);
            }
        }
        /** 7. Move Customer 2’s newsletter information to Customer 1. */
        $newsletter = $this->subscriberFactory->create()->loadByCustomerId($fromCustomer->getId());
        if ($newsletter->getId() && $newsletter->getId() !== null) {
            $newsletter->setCustomerId($toCustomerId);
            $newsletter->setEmail($toCustomer->getEmail());
            $this->subscriberResource->save($newsletter);
        }
        /** 8. Move Customer 2’s gift registries information to Customer 1. */

        $gifts = $this->getGiftsByCustomerId($fromCustomerId);
        if (count($gifts) > 0) {
            foreach ($gifts as $gift) {
                $fromGift = $this->giftFactory->create()->loadByUrlKey($gift['url_key']);
                $fromGift->setCustomerId($toCustomerId);
                $this->giftResourceModel->save($fromGift);
            }
        }
        /** 9. For Customer 2’s Store Credit Balance, update Customer 1’s balance with it. */
        $fromBalance = $this->balanceFactory->create()->setCustomerId($fromCustomerId)->loadByCustomer();
        $amount = $fromBalance->getAmount();
        if ($amount !== null && $amount > 0) {
            $fromBalance->setAmount(0);
            $this->balanceResource->save($fromBalance);
            $toBalance = $this->balanceFactory->create()->setCustomerId($toCustomerId)->loadByCustomer();
            $toBalance->setAmount($toBalance->getAmount() + $amount);
            $this->balanceResource->save($toBalance);
        }
        /** 10. For Customer 2’s Reward Point Balance, update Customer 1’s balance with it. */
        $fromReward = $this->rewardFactory->create()->setCustomerId($fromCustomerId)->loadByCustomer();
        $balance = $fromReward->getPointsBalance();
        if ($balance !== null && $balance > 0) {
            $toReward = $this->rewardFactory->create()->setCustomerId($toCustomerId)->loadByCustomer();
            $fromReward->setPointsBalance(0);
            $this->rewardResource->save($fromReward);
            $toReward->setPointsBalance($toReward->getPointsBalance() + $balance);
            $this->rewardResource->save($toReward);
        }
        /** 11. When all the above is done, set Customer 2 to disable */
        $this->customerRepository->save($fromCustomer);
        $this->customerRepository->save($toCustomer);
        $this->customerResource->getConnection()->update(
            $this->customerResource->getTable('customer_entity'),
            [
                'failures_num' => 10,
                'lock_expires' => $this->dateTime->formatDate(time() + 999999),
                'is_active' => 0
            ],
            $this->customerResource->getConnection()->quoteInto('entity_id = ?', $fromCustomerId)
        );
    }

    /**
     * @param $data
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     * @throws CouldNotSaveException
     */
    public function splitCustomer($data)
    {
        try {
            if ($this->customerRepository->get($data['email'])->getId() !== null) {
                $customerFlag = true;
            }
        } catch (NoSuchEntityException $e) {
            $this->_logger->info('Createing new customer with email ' . $data['email']);
        } catch (LocalizedException $e) {
            $this->_logger->info('Creating new customer with email ' . $data['email']);
        }
        if (!isset($customerFlag)) {
            $customer = $this->customerFactory->create();
            $customer->setEmail($data['email']);
            $customer->setFirstname($data['firstname']);
            $customer->setLastname($data['lastname']);
            $this->customerRepository->save($customer);
        }
        $newCustomer = $this->customerRepository->get($data['email']);

        if (isset($data['address'])) {
            foreach ($data['address'] as $addressId) {
                $address = $this->addressRepository->getById($addressId);
                $address->setCustomerId($newCustomer->getId());
                $this->addressRepository->save($address);
            }
        }

        if (isset($data['order'])) {
            foreach ($data['order'] as $orderId) {
                $order = $this->orderRepository->get($orderId);
                $this->changeOrder($order, $newCustomer);
            }
        }

        if (isset($data['return'])) {
            foreach ($data['return'] as $returnId) {
                $rma = $this->rmaRepository->get($returnId);
                $rma->setCustomerId($newCustomer->getId());
                if ($rma->getCustomerCustomEmail() !== null) {
                    $rma->setCustomerCustomEmail($newCustomer->getEmail());
                }
                $this->rmaRepository->save($rma);
            }
        }
    }

    /**
     * @param $customerId
     * @return array
     */
    protected function getGiftsByCustomerId($customerId)
    {
        $connection = $this->giftResourceModel->getConnection();
        try {
            $select = $connection->select()->from(['e' => $this->giftResourceModel->getMainTable()])
                ->where('customer_id = ?', $customerId);
            $data = $connection->fetchAll($select);
        } catch (LocalizedException $e) {
            return [];
        }
        return $data;
    }

    /**
     * @param Order|OrderInterface $order
     * @param CustomerInterface $toCustomer
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    protected function changeOrder($order, CustomerInterface $toCustomer)
    {
        try {
            $quote = $this->quoteRepository->get($order->getQuoteId());
            $quote->setCustomer($toCustomer);
            /** a. Also change the customer_email on the order to be Customer 1’s email. */
            $quote->setCustomerEmail($toCustomer->getEmail());
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            if ($quote->getItems() !== null) {
                foreach ($quote->getItems() as $quoteItem) {
                    /** @var Item $orderItem */
                    $orderItem = $this->quoteToOrder->convert($quoteItem);
                    $origOrderItemNew = $order->getItemByQuoteItemId($quoteItem->getId());
                    if ($origOrderItemNew) {
                        $origOrderItemNew->addData($orderItem->getData());
                    } else {
                        if ($quoteItem->getParentItem()) {
                            /** @var \Magento\Sales\Model\Order\Item $quoteItemId */
                            $parentItem = $orderItem->getParentItem();
                            $quoteItemId = $parentItem->getId();
                            /** @var Item $item */
                            $item = $order->getItemByQuoteItemId($quoteItemId);
                            $orderItem->setParentItem($item);
                        }
                        $order->addItem($orderItem);
                    }
                }
            }
            $order->setSubtotal($quote->getSubtotal())
                ->setBaseSubtotal($quote->getBaseSubtotal())
                ->setGrandTotal($quote->getGrandTotal())
                ->setBaseGrandTotal($quote->getBaseGrandTotal())
                ->setCustomerEmail($toCustomer->getEmail())
                ->setCustomerId($toCustomer->getId());
            $this->quoteRepository->save($quote);
        } catch (NoSuchEntityException $e) {
            $order->setCustomerEmail($toCustomer->getEmail());
            $order->setCustomerPrefix($toCustomer->getPrefix());
            $order->setCustomerFirstname($toCustomer->getFirstname());
            $order->setCustomerMiddlename($toCustomer->getMiddlename());
            $order->setCustomerLastname($toCustomer->getLastname());
            $order->setCustomerSuffix($toCustomer->getSuffix());
            $order->setCustomerGroupId($toCustomer->getGroupId());
            $order->setCustomerDob($toCustomer->getDob());
            $order->setCustomerTaxvat($toCustomer->getTaxvat());
            $order->setCustomerGender($toCustomer->getGender());
            $order->setCustomerId($toCustomer->getId());
        }
        $this->orderRepository->save($order);
    }
}
