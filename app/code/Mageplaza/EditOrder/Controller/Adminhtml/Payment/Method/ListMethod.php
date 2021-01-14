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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Payment\Method;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment\ListPayment;
use Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\PaymentMethod;

/**
 * Class ListMethod
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Payment\Method
 */
class ListMethod extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var Create
     */
    protected $orderCreate;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * ListMethod constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param OrderFactory $orderFactory
     * @param Create $orderCreate
     * @param QuoteSession $quoteSession
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        OrderFactory $orderFactory,
        Create $orderCreate,
        QuoteSession $quoteSession
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderFactory = $orderFactory;
        $this->orderCreate = $orderCreate;
        $this->quoteSession = $quoteSession;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $resultLayout = $this->resultLayoutFactory->create();
        $this->quoteSession->clearStorage();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        $this->orderCreate->initFromOrder($order);

        if ($order->getPayment()) {
            $paymentMethodCode = $order->getPayment()->getMethod();

            $child = $resultLayout->getLayout()
                ->createBlock(ListPayment::class, 'edit_order_payment_form', [
                    'data' => [
                        'offline_only' => $order->getState() !== Order::STATE_NEW
                    ]
                ])
                ->setTemplate('Magento_Sales::order/create/billing/method/form.phtml')
                ->setPaymentCode($paymentMethodCode);

            $listMethodHtml = $resultLayout->getLayout()
                ->createBlock(PaymentMethod::class)
                ->setTemplate('Mageplaza_EditOrder::order/edit/payment/method/list.phtml')
                ->setChild('billing_method', $child)
                ->toHtml();

            $result->setData(['success' => $listMethodHtml]);
        }

        return $result;
    }
}
