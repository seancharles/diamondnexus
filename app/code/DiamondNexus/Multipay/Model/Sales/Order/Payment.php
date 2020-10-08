<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model\Sales\Order;

use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Processor;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;


/**
 * Class Payment
 * @package DiamondNexus\Multipay\Model\Sales\Order
 */
class Payment extends Order\Payment
{

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        EncryptorInterface $encryptor,
        CreditmemoFactory $creditmemoFactory,
        PriceCurrencyInterface $priceCurrency,
        TransactionRepositoryInterface $transactionRepository,
        ManagerInterface $transactionManager,
        BuilderInterface $transactionBuilder,
        Processor $paymentProcessor,
        OrderRepositoryInterface $orderRepository,
        TransactionFactory $transactionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        CreditmemoManager $creditmemoManager = null
    )
    {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $creditmemoFactory,
            $priceCurrency,
            $transactionRepository,
            $transactionManager,
            $transactionBuilder,
            $paymentProcessor,
            $orderRepository,
            $resource,
            $resourceCollection,
            $data,
            $creditmemoManager
        );
        $this->transactionFactory = $transactionFactory;
    }


    /**
     * @return $this|Payment
     */
    public function place()
    {
        $order = $this->getOrder();

        // TODO: save in table info
        try {
            $methodInstance = $this->getMethodInstance();

            $payment = $order->getPayment();
            $method = $payment->getMultipayPaymentMethod();
            $amount = $payment->getMultipayAmount();

            if ($methodInstance->getCode() === Constant::MULTIPAY_METHOD) {
                $this->_eventManager->dispatch('sales_order_payment_place_start', ['payment' => $this]);

                $this->setAmountOrdered($order->getTotalDue());
                $this->setBaseAmountOrdered($order->getBaseTotalDue());
                $this->setShippingAmount($order->getShippingAmount());
                $this->setBaseShippingAmount($order->getBaseShippingAmount());

                $methodInstance->setStore($order->getStoreId());

                $orderState = Order::STATE_NEW;
                $orderStatus = $methodInstance->getConfigData('order_status');
                if (!$orderStatus || $order->getIsVirtual()) {
                    $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
                }

                switch ($method) {
                    case Constant::MULTIPAY_CASH_METHOD:

                        // create a cash transaction
                        $transaction = $this->transactionFactory->create();
                        $transaction->setData([
                            'order_id' => $order->getId(),
                            'transaction_type' => Constant::MULTIPAY_SALE_ACTION,
                            'payment_method' => Constant::MULTIPAY_CASH_METHOD,
                            'amount' => $payment->getMultipayAmount(),
                            'tendered' => $payment->getMultipayCashTendered(),
                            'change' => $payment->getMultipayChangeDue(),
                            'transaction_timestamp' => time(),
                        ]);

                        $transaction->save();

                        $order->setBaseTotalPaid($amount);
                        $order->setTotalPaid($amount);

                        break;

                    case Constant::MULTIPAY_AFFIRM_OFFLINE_METHOD:

                        // create a cash transaction
                        $transaction = $this->transactionFactory->create();
                        $transaction->setData([
                            'order_id' => $order->getId(),
                            'transaction_type' => Constant::MULTIPAY_SALE_ACTION,
                            'payment_method' => Constant::MULTIPAY_CASH_METHOD,
                            'amount' => $payment->getMultipayAmount(),
                            'transaction_timestamp' => time(),
                        ]);

                        $transaction->save();

                        $order->setBaseTotalPaid($amount);
                        $order->setTotalPaid($amount);

                        break;

                    case Constant::MULTIPAY_QUOTE_METHOD:

                        // we don't really do anything with quotes yet

                        // update the amount paid on the order
                        $order->setBaseTotalPaid(0);
                        $order->setTotalPaid(0);

                        // set the order status to quote
                        $orderStatus = Constant::STATE_QUOTE;

                        break;
                }

                //$isCustomerNotified = (null !== $orderIsNotified) ? $orderIsNotified : $order->getCustomerNoteNotify();
                $isCustomerNotified = $order->getCustomerNoteNotify();
                $message = $order->getCustomerNote();

                // add message if order was put into review during authorization or capture
                if ($order->getState() === Order::STATE_PAYMENT_REVIEW) {
                    if ($message) {
                        $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
                    }
                } // add message to history if order state already declared
                elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
                    $order->setState($orderState);
                    $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
                } // set order state
                elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
                    $order->setState($orderState);
                    $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
                }

                $this->_eventManager->dispatch('sales_order_payment_place_end', ['payment' => $this]);

                return $this;

            } else {
                return parent::place();
            }
        } catch (LocalizedException $e) {
            $this->_logger->error($e);
            return parent::place();
        }
    }
}
