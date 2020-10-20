<?php
namespace DiamondNexus\Multipay\Block\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class Paynow extends Template
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \DiamondNexus\Multipay\Model\ResourceModel\Transaction
     */
    protected $transaction;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        \DiamondNexus\Multipay\Model\ResourceModel\Transaction $transaction
    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->transaction = $transaction;
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        $orderId = false;
        if ($this->hasData('order')) {
            $orderId = $this->getData('order_id');
        }
        return $this->getUrl(
            'diamondnexus/order/paynowAction',
            [
                'order_id' => $orderId
            ]
        );
    }

    /**
     * @return float|int
     */
    public function getBalanceAmount()
    {
        /** @var Order $order */
        $order = $this->orderRepository->get($this->getData('order_id'));
        $fullPrice = (float)$order->getGrandTotal();
        $payedPart = 0;
        try {
            $transactions = $this->transaction->getAllTransactionsByOrderId($this->getData('order_id'));
        foreach ($transactions as $transaction) {
            $payedPart += (float)$transaction['amount'];
            if ($transaction['amount'] == 0) {
                $payedPart += (float)$transaction['tendered'];
            }
        }
        return $fullPrice - $payedPart;
        } catch (LocalizedException $e) {
            return 0;
        }
    }
}
