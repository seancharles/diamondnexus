<?php
namespace DiamondNexus\Multipay\Block\Order;

use DiamondNexus\Multipay\Model\ResourceModel\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

abstract class AbstractPay extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        Transaction $transaction,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
        $this->messageManager = $messageManager;
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->getUrlAction('paynowAction');
    }

    /**
     * @param bool $orderId
     * @return string
     */
    public function getPaypalUrl($orderId = false)
    {
        return $this->getUrlAction('paypal', $orderId);
    }

    /**
     * @return string
     */
    public function getPaypalActionUrl()
    {
        return $this->getUrlAction('paypalAction');
    }

    /**
     * @return float|int
     */
    public function getBalanceAmount()
    {
        /** @var Order $order */
        $order = $this->orderRepository->get($this->getData('order_id'));
        $fullPrice = round($order->getGrandTotal(), 2);
        $payedPart = 0;
        try {
            $transactions = $this->transaction->getAllTransactionsByOrderId($this->getData('order_id'));
            foreach ($transactions as $transaction) {
                $payedPart += (float)$transaction['amount'];
                if ($transaction['amount'] == 0) {
                    $payedPart += round($transaction['tendered'], 2);
                }
            }
            return $fullPrice - $payedPart;
        } catch (LocalizedException $e) {
            return 0;
        }
    }

    /**
     * @return string|null
     */
    public function getIncrementId()
    {
        return $this->orderRepository->get($this->getData('order_id'))->getIncrementId();
    }

    /**
     * @param $where
     * @param int|bool $orderId
     * @return string
     */
    protected function getUrlAction($where, $orderId = false)
    {
        if ($this->hasData('order_id')) {
            $orderId = $this->getData('order_id');
        }
        return $this->getUrl(
            'diamondnexus/order/' . $where,
            [
                'order_id' => $orderId
            ]
        );
    }
}
