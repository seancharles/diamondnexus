<?php

namespace DiamondNexus\Multipay\Block\Adminhtml\Order\View;

use DiamondNexus\Multipay\Block\Adminhtml\Order\AbstractPayment;
use DiamondNexus\Multipay\Model\Constant;
use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class AddPaymentModalBox
 * @package DiamondNexus\Multipay\Block\Adminhtml\Order
 */
class Payments extends AbstractPayment
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var PriceCurrencyInterface
     */
    protected $currency;

    /**
     * Payments constructor.
     * @param Context $context
     * @param Transaction $transactionResource
     * @param OrderRepositoryInterface $orderRepository
     * @param PriceCurrencyInterface $currency
     * @param array $data
     */
    public function __construct(
        Context $context,
        Transaction $transactionResource,
        OrderRepositoryInterface $orderRepository,
        PriceCurrencyInterface $currency,
        array $data = []
    ) {
        parent::__construct($context, $transactionResource, $data);
        $this->orderRepository = $orderRepository;
        $this->currency = $currency;
    }

    /**
     * @return bool
     */
    public function isMultipay()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        return $order->getPayment()->getMethod() == 'multipay';
    }

    /**
     * @return array|false
     */
    public function getTransactions()
    {
        try {
            return $this->resource->getAllTransactionsByOrderId($this->getRequest()->getParam('order_id'));
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * @param array $transaction
     * @return string
     */
    public function transactionToHtml(array $transaction)
    {
        $time = $this->formatDate($transaction['transaction_timestamp'], 2, true);
        $method = Constant::MULTIPAY_METHOD_LABEL[$transaction['payment_method']];
        $amount = $this->currency->convertAndFormat($transaction['amount']);
        return $time . ' - ' . $method . ': ' . $amount;
    }
}
