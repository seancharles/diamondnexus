<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace DiamondNexus\Multipay\Model\Sales\Order;

use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\TransactionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\CreditmemoManagementInterface as CreditmemoManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;



/**
 * Class Payment
 * @package DiamondNexus\Multipay\Model\Sales\Order
 */
class Payment extends \Magento\Sales\Model\Order\Payment
{

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * @var  CreditmemoManager
     */
    protected $creditmemoManager;

    public function __construct(
        \DiamondNexus\Multipay\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
        ManagerInterface $transactionManager,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,
        \Magento\Sales\Model\Order\Payment\Processor $paymentProcessor,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        CreditmemoManager $creditmemoManager = null
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoManager = $creditmemoManager;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $resource,
            $resourceCollection,
            $data,
            $creditmemoManager
        );
    }


    public function place()
    {
        $order = $this->getOrder();

        // load payment model
        try {
            $methodInstance = $this->getMethodInstance();
        } catch (LocalizedException $e) {
        }

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
                    $orderStatus = DiamondNexus_Multipay_Model_Constant::STATE_QUOTE;

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
            }
            // add message to history if order state already declared
            elseif ($order->getState() && ($orderStatus !== $order->getStatus() || $message)) {
                $order->setState($orderState);
                $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
            }
            // set order state
            elseif (($order->getState() != $orderState) || ($order->getStatus() != $orderStatus) || $message) {
                $order->setState($orderState);
                $order->addStatusToHistory($order->getStatus(), $message, $isCustomerNotified);
            }

            $this->_eventManager->dispatch('sales_order_payment_place_end', ['payment' => $this]);

            return $this;

        } else {
            parent::place();
        }
    }
}
