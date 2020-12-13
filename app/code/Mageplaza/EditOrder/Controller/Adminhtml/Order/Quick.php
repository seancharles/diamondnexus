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

namespace Mageplaza\EditOrder\Controller\Adminhtml\Order;

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
use Magento\Sales\Model\OrderFactory;

/**
 * Class Quick
 * @package Mageplaza\EditOrder\Controller\Adminhtml\Order
 */
class Quick extends Action
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
     * Quick constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param OrderFactory $orderFactory
     * @param QuoteSession $quoteSession
     * @param Create $orderCreate
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        OrderFactory $orderFactory,
        QuoteSession $quoteSession,
        Create $orderCreate
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderFactory = $orderFactory;
        $this->quoteSession = $quoteSession;
        $this->orderCreate = $orderCreate;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->quoteSession->clearStorage();
        $result = $this->resultJsonFactory->create();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderFactory->create()->load($orderId);
        $paymentMethodCode = '';

        $this->quoteSession->setUseOldShippingMethod(true);
        $this->orderCreate->initFromOrder($order);

        if ($order->getPayment()) {
            $paymentMethodCode = $order->getPayment()->getMethod();
        }

        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addHandle('mpeditorder_quick_edit');
        $resultLayout->getLayout()->getBlock('billing_method')->setPaymentCode($paymentMethodCode);
        $resultLayout->getLayout()->getBlock('mpeditorder.order.info')->setOrder($order);
        $resultLayout->getLayout()->getBlock('mpeditorder.customer')->setOrder($order);
        $resultLayout->getLayout()->getBlock('mpeditorder.billing.address')->setOrder($order);
        $resultLayout->getLayout()->getBlock('mpeditorder.shipping.address')->setOrder($order);
        $resultLayout->getLayout()->getBlock('mpeditorder.shipping.method')->setOrderId($orderId);
        $resultLayout->getLayout()->getBlock('mpeditorder.quick.edit')->setOrder($order);
        $html = $resultLayout->getLayout()->getBlock('mpeditorder.quick.edit')->toHtml();
        $result->setData(['success' => $html]);

        return $result;
    }
}
