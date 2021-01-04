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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Tax\Helper\Data;

/**
 * Class ShippingMethod
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class ShippingMethod extends Form
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * ShippingMethod constructor.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param Data $taxData
     * @param OrderRepositoryInterface $orderRepository
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        Data $taxData,
        OrderRepositoryInterface $orderRepository,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteFactory    = $quoteFactory;

        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
    }

    /**
     * @return string
     */
    public function getActionForm()
    {
        return $this->getUrl(
            'mpeditorder/shipping_method/save',
            [
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getEditShippingButtonUrl()
    {
        return $this->getUrl(
            'mpeditorder/shipping_method/listMethod',
            [
                'order_id'    => $this->getOrder()->getId(),
                'mpeditorder' => true,
                'form_key'    => $this->getFormKey()
            ]
        );
    }

    /**
     * @return OrderInterface
     */
    public function getOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id') ?: $this->getOrderId();

        return $this->orderRepository->get($orderId);
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    public function isMethodActive($code)
    {
        return $code === $this->getOrder()->getShippingMethod();
    }

    /**
     * Get shipping amount
     *
     * @return float
     */
    public function getBaseTotalShippingFee()
    {
        $shipAmount = $this->getBaseShippingAmount();
        $shipTax = $this->getBaseShippingTaxPercent();
        $shipDiscount = $this->getBaseShippingDiscountAmount();
        $totalShipFee = $shipAmount + ($shipAmount * $shipTax) / 100 - $shipDiscount;

        return $totalShipFee;
    }

    /**
     * @return float|int
     */
    public function getBaseShippingAmount()
    {
        return $this->getOrder()->getBaseShippingAmount();
    }

    /**
     * @return float|int
     */
    public function getBaseShippingTaxPercent()
    {
        $baseShippingAmount = $this->getBaseShippingAmount();

        if ($baseShippingAmount != 0) { //prevent null and 0.0
            return $this->getOrder()->getBaseShippingTaxAmount() * 100 / $baseShippingAmount;
        }

        return 0;
    }

    /**
     * @return float|int|null
     */
    public function getBaseShippingDiscountAmount()
    {
        return $this->getOrder()->getBaseShippingDiscountAmount();
    }

    /**
     * @param $_rate
     *
     * @return array
     */
    public function getBaseShippingData($_rate)
    {
        if ($this->isMethodActive($_rate->getCode())) {
            return [
                'ship_amount'          => $this->getBaseShippingAmount(),
                'ship_tax_percent'     => $this->getBaseShippingTaxPercent(),
                'ship_discount_amount' => $this->getBaseShippingDiscountAmount(),
                'total_fee'            => $this->getBaseTotalShippingFee()
            ];
        }

        return [
            'ship_amount'          => $_rate->getPrice(),
            'ship_tax_percent'     => 0,
            'ship_discount_amount' => 0,
            'total_fee'            => $_rate->getPrice()
        ];
    }
}
