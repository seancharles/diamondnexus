<?php

namespace DiamondNexus\Multipay\Helper;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use DiamondNexus\Multipay\Model\Constant;
use League\ISO3166\ISO3166;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\ValidatorException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use PayPal\Braintree\Model\Adapter\BraintreeAdapter;

class Data extends AbstractHelper
{
    /**
     * @var BraintreeAdapter
     */
    protected $brainTreeAdapter;

    /**
     * @var ISO3166
     */
    protected $iso3166;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * Data constructor.
     * @param Context $context
     * @param BraintreeAdapter $braintreeAdapter
     * @param ISO3166 $iso3166
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $braintreeAdapter,
        ISO3166 $iso3166,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->brainTreeAdapter = $braintreeAdapter;
        $this->iso3166 = $iso3166;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param OrderInterface $order
     * @return Error|Successful
     * @throws ValidatorException
     */
    public function sendToBraintree(OrderInterface $order)
    {
        // removing for PCI compliance
        //return $this->brainTreeAdapter->sale($attributes);

        return false;
    }

    public function updateOrderStatus($post, $order)
    {
        $status = '';
        if ($post[Constant::OPTION_TOTAL_DATA] == 1) {
            $status = OrderModel::STATE_PROCESSING;
        }
        if ($post[Constant::OPTION_TOTAL_DATA] == 2) {
            $status = 'pending';
            if ($post[Constant::OPTION_PARTIAL_DATA] == $post[Constant::AMOUNT_DUE_DATA]) {
                $status = OrderModel::STATE_PROCESSING;
            }
        }
        if ($post == Constant::MULTIPAY_QUOTE_METHOD) {
            $status = 'quote';
        }
        if ($order->getStatus() !== $status) {
            $order->setStatus($status)->setState($status);
        }
        $this->orderRepository->save($order);
    }

    public function parseCurrency($amount = 0)
    {
        $return = (double) filter_var($amount, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (abs($return) > 0) {
            return bcdiv($return, 1, 2);
        } else {
            return 0;
        }
    }
}
