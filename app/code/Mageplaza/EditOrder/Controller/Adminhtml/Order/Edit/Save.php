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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Sales\Block\Adminhtml\Order\Payment as OrderPayment;
use Magento\Sales\Block\Adminhtml\Order\Totals;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment as PaymentResource;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment\Info as PaymentInfo;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Totals\Tax;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Logs;
use Mageplaza\EditOrder\Model\LogsFactory;
use Mageplaza\EditOrder\Model\Order\Edit as EditModel;
use Mageplaza\EditOrder\Model\Order\Total as OrderTotal;
use Psr\Log\LoggerInterface;
use Zend\Uri\Uri;

/**
 * Class Save
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Order\Edit
 */
class Save extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var PaymentResource
     */
    protected $paymentResource;

    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var LogsFactory
     */
    protected $logsFactory;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var OrderTotal
     */
    protected $orderTotal;

    /**
     * @var EditModel
     */
    protected $editModel;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * Save constructor.
     *
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PaymentResource $paymentResource
     * @param PaymentFactory $paymentFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param OrderFactory $orderFactory
     * @param LogsFactory $logsFactory
     * @param Session $authSession
     * @param RemoteAddress $remoteAddress
     * @param QuoteFactory $quoteFactory
     * @param HelperData $_helperData
     * @param OrderTotal $orderTotal
     * @param EditModel $editModel
     * @param QuoteSession $quoteSession
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PaymentResource $paymentResource,
        PaymentFactory $paymentFactory,
        LayoutFactory $resultLayoutFactory,
        OrderFactory $orderFactory,
        LogsFactory $logsFactory,
        Session $authSession,
        RemoteAddress $remoteAddress,
        QuoteFactory $quoteFactory,
        HelperData $_helperData,
        OrderTotal $orderTotal,
        EditModel $editModel,
        QuoteSession $quoteSession,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->paymentResource     = $paymentResource;
        $this->paymentFactory      = $paymentFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->orderFactory        = $orderFactory;
        $this->logsFactory         = $logsFactory;
        $this->authSession         = $authSession;
        $this->remoteAddress       = $remoteAddress;
        $this->quoteFactory        = $quoteFactory;
        $this->_helperData         = $_helperData;
        $this->orderTotal          = $orderTotal;
        $this->editModel           = $editModel;
        $this->quoteSession        = $quoteSession;
        $this->_logger             = $logger;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $result  = $this->resultJsonFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        $order   = $this->getOrder();
        $uri     = new Uri();
        $newData = $uri->setQuery($this->getRequest()->getParam('newData'))->getQueryAsArray();
        $oldData = $uri->setQuery($this->getRequest()->getParam('oldData'))->getQueryAsArray();

        $resultData = [];
        $diff       = $this->_helperData->arrayDifferent($newData, $oldData);
        if (!count($diff)) {
            $diff = $this->_helperData->arrayDifferent($oldData, $newData);
        }

        $isEdited = $this->_helperData->isEdited($diff);
        if ($isEdited) {
            /** @var Logs $log */
            $log     = $this->logsFactory->create();
            $logData = $this->getLogData($newData, $oldData, $diff);
            try {
                $log->addData($logData)->save();
            } catch (Exception $e) {
                $this->_logger->critical($e->getMessage());
            }

            /** check if empty item */
            if (isset($oldData['item']) && !isset($newData['item'])) {
                $resultData['items'] = [
                    'error'   => true,
                    'message' => __('There must always be items')
                ];

                return $result->setData($resultData);
            }

            /** save order data */
            if ($orderId && (isset($newData['order']) || isset($newData['item']))) {
                $resultData = $this->setPostData($newData, $diff);
            }

            if (isset($newData['payment'])) {
                $resultData['payment_method'] = $this->editPaymentMethod($order, $newData['payment']);
            }

            /** result error */
            foreach ($resultData as $key => $value) {
                if (isset($resultData[$key]['error'])) {
                    $resultData = [
                        $key => $value
                    ];
                }
            }
        }

        $result->setData($resultData);

        return $result;
    }

    /**
     * @param Order $order
     * @param array $newData
     *
     * @return array
     */
    public function editPaymentMethod($order, $newData)
    {
        $paymentId   = 0;
        $paymentData = $newData;

        if ($order->getPayment()) {
            $paymentId = $order->getPayment()->getEntityId();
        }
        /** @var Payment $payment */
        $payment = $this->paymentFactory->create();
        $this->paymentResource->load($payment, $paymentId);
        $order->setPayment($payment);
        $payment->addData($paymentData);

        try {
            $payment->save();
            $this->messageManager->addSuccessMessage(__('This order has been updated!'));
            $resultData = ['success' => $this->getPaymentHtml($order)];
        } catch (Exception $e) {
            $resultData['payment_save_error'] = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $resultData;
    }

    /**
     * @param array $data
     * @param array $diffData
     *
     * @return array
     * @throws LocalizedException
     */
    public function setPostData($data, $diffData)
    {
        $result = [];
        $order  = $this->getOrder();

        if (isset($data['item'])) {
            $result['items'] = $this->editItems($order);
            $this->applyCoupon($data, $order);
        }

        if (isset($data['order'])) {
            $orderData = $data['order'];

            if (!is_array($diffData)) {
                return $result;
            }

            if (isset($diffData['order']['billing_address'])) {
                $result['billing_address'] = $this->editModel->setAddress(
                    $order->getId(),
                    $orderData['billing_address']
                );
            }

            if (isset($diffData['order']['shipping_address'])) {
                $result['shipping_address'] = $this->editModel->setAddress(
                    $order->getId(),
                    $orderData['shipping_address']
                );
            }

            if (isset($diffData['order']['info'])) {
                $result['info'] = $this->editModel->setInfoData($order, $orderData['info']);
            }

            if (isset($diffData['order']['customer'])) {
                $result['customer'] = $this->editModel->setCustomerData($order, $orderData['customer']);
            }

            if (isset($diffData['order']['shipping_method']) || isset($diffData['method_detail'])) {
                $shipMethod = $orderData['shipping_method'];

                if ($shipMethod === 'freeshipping_freeshipping') {
                    $shipData = [
                        'ship_amount'          => 0,
                        'ship_tax_percent'     => 0,
                        'ship_discount_amount' => 0,
                        'total_fee'            => 0,
                        'ship_description'     => __('Free Shipping'),
                        'method'               => 'freeshipping_freeshipping',
                        'type'                 => OrderTotal::TYPE_COLLECT_SHIPPING
                    ];
                } else {
                    $shipData           = $data['method_detail'][$shipMethod];
                    $shipData['method'] = $shipMethod;
                    $shipData['type']   = OrderTotal::TYPE_COLLECT_SHIPPING;
                }

                $result['shipping_method'] = $this->setShippingMethod($shipData);
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param Order $order
     *
     * @return array
     */
    public function applyCoupon($data, $order)
    {
        if (isset($data['mp_coupon_code'])) {
            $order->setCouponCode($data['mp_coupon_code']);
        } else {
            $order->setCouponCode(null);
        }

        try {
            $order->save();
            $result = [
                'success' => true
            ];
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
     *
     * @return array
     */
    public function editItems($order)
    {
        $quote = $this->quoteFactory->create()->load($this->quoteSession->getQuoteId());
        try {
            $status = $this->orderTotal->saveOrder($order, $this->editModel->getQuoteItemsData($quote));
            if (isset($status['success'])) {
                $result = [
                    'success' => [
                        'itemsHtml'      => $this->getItemsHtml($this->getOrder()),
                        'orderTotalHtml' => $this->getOrderTotalHtml($this->getOrder()),
                    ]
                ];
            } else {
                $result = [
                    'error'   => true,
                    'message' => $status['error']
                ];
            }
        } catch (Exception $e) {
            $result = [
                'error'   => true,
                'message' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Get html items order
     *
     * @param Order $order
     *
     * @return string
     */
    public function getItemsHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addHandle('mpeditorder_items_view');
        $resultLayout->getLayout()->getBlock('order_tab_info')->setCurrentOrder($order);

        return $resultLayout->getLayout()->getBlock('order_items')->toHtml();
    }

    /**
     * @param array $newData
     * @param array $oldData
     * @param array $diff
     *
     * @return mixed
     */
    public function getLogData($newData, $oldData, $diff)
    {
        $oldTotalData = [];

        /** @var Order $order */
        $orderId = $this->getRequest()->getParam('order_id');
        $order   = $this->orderFactory->create()->load($orderId);

        if (isset($diff['method_detail']) || isset($diff['order']['shipping_method']) || isset($diff['item'])) {
            $oldTotalData = [
                'subtotal'        => $order->getBaseSubtotal(),
                'shipping_amount' => $order->getBaseShippingAmount(),
                'tax_amount'      => $order->getBaseTaxAmount(),
                'discount_amount' => $order->getBaseDiscountAmount(),
                'grand_total'     => $order->getBaseGrandTotal(),
                'total_paid'      => $order->getBaseTotalPaid(),
                'total_refund'    => $order->getBaseTotalRefunded(),
                'total_due'       => $order->getBaseTotalDue(),
            ];
        }

        $data['order_id']       = $order->getId();
        $data['editor']         = $this->getAdminUserName();
        $data['editor_id']      = $this->getAdminUserId();
        $data['editor_ip']      = $this->remoteAddress->getRemoteAddress();
        $data['order_number']   = $order->getIncrementId();
        $data['edited_type']    = $this->_helperData->getEditedType($diff);
        $data['old_data']       = HelperData::jsonEncode($oldData);
        $data['new_data']       = HelperData::jsonEncode($newData);
        $data['old_total_data'] = HelperData::jsonEncode($oldTotalData);
        $data['created_at']     = date(DateTime::DATETIME_PHP_FORMAT);

        return $data;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * @param array $shipData
     *
     * @return array
     */
    public function setShippingMethod($shipData)
    {
        try {
            $this->orderTotal->saveOrder($this->getOrder(), $shipData);
            $result = [
                'success' => [
                    'orderTotalHtml'     => $this->getOrderTotalHtml($this->getOrder()),
                    'shippingMethodHtml' => $this->getShippingMethodHtml($this->getOrder())
                ]
            ];
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
     *
     * @return mixed
     */
    public function getPaymentHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $child        = $resultLayout->getLayout()->createBlock(OrderPayment::class);

        return $resultLayout->getLayout()->createBlock(PaymentInfo::class)
            ->setTemplate('Mageplaza_EditOrder::order/edit/payment/method/info.phtml')
            ->setCurrentOrder($order)
            ->setChild('order_payment', $child)
            ->toHtml();
    }

    /**
     * Get order total html
     *
     * @param Order $order
     *
     * @return mixed
     */
    public function getOrderTotalHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        /** set tax row */
        $tax = $resultLayout->getLayout()->createBlock(Tax::class)
            ->setCurrentOrder($order)
            ->setTemplate('Magento_Sales::order/totals/tax.phtml');

        return $resultLayout->getLayout()->createBlock(Totals::class)
            ->setOrder($order)
            ->setTemplate('Magento_Sales::order/totals.phtml')
            ->setChild('tax', $tax)
            ->toHtml();
    }

    /**
     * Get shipping method html
     *
     * @param Order $order
     *
     * @return string
     */
    public function getShippingMethodHtml($order)
    {
        $resultLayout = $this->resultLayoutFactory->create();

        return $resultLayout->getLayout()
            ->createBlock(AbstractOrder::class)->setOrder($order)
            ->setTemplate('Magento_Shipping::order/view/info.phtml')
            ->toHtml();
    }

    /**
     * @return int
     */
    public function getAdminUserId()
    {
        $user = $this->authSession->getUser();

        if ($user) {
            return $user->getId();
        }

        return 0;
    }

    /**
     * @return mixed|string
     */
    public function getAdminUserName()
    {
        $user = $this->authSession->getUser();

        if ($user) {
            return $user->getFirstName() . ' ' . $user->getLastName();
        }

        return '';
    }
}
