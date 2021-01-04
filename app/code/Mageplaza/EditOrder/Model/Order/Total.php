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

namespace Mageplaza\EditOrder\Model\Order;

use Exception;
use Magento\Backend\Model\Session\Quote as QuoteSession;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;
use Mageplaza\EditOrder\Helper\Data as HelperData;
use Mageplaza\EditOrder\Model\Quote\QuoteManagement;

/**
 * Class Total
 * @package Mageplaza\EditOrder\Model\Order
 */
class Total
{
    const TYPE_COLLECT_ITEMS = 'items';
    const TYPE_COLLECT_SHIPPING = 'shipping';

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteSession
     */
    protected $quoteSession;

    /**
     * @var OrderManagement
     */
    protected $orderManagement;

    /**
     * @var StockItemInterface
     */
    protected $stockItem;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Total constructor.
     *
     * @param QuoteManagement $quoteManagement
     * @param QuoteFactory $quoteFactory
     * @param QuoteSession $quoteSession
     * @param OrderManagement $orderManagement
     * @param StockItemInterface $stockItem
     * @param HelperData $helperData
     */
    public function __construct(
        QuoteManagement $quoteManagement,
        QuoteFactory $quoteFactory,
        QuoteSession $quoteSession,
        OrderManagement $orderManagement,
        StockItemInterface $stockItem,
        HelperData $helperData
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->quoteFactory = $quoteFactory;
        $this->quoteSession = $quoteSession;
        $this->orderManagement = $orderManagement;
        $this->stockItem = $stockItem;
        $this->_helperData = $helperData;
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function saveOrder($order, $data)
    {
        $total = $this->collectTotals($order, $data);
        if (count($total)) {
            if (isset($total['ship_amount'])) {
                $order->setShippingMethod($data['method'])
                    ->setShippingAmount($total['ship_amount'])
                    ->setShippingTaxAmount($total['ship_tax_amount'])
                    ->setShippingDiscountAmount($total['ship_discount_amount'])
                    ->setShippingDescription($total['ship_description'])
                    ->setBaseShippingTaxAmount($total['ship_tax_amount'])
                    ->setBaseShippingDiscountAmount($total['ship_discount_amount'])
                    ->setBaseShippingAmount($total['ship_amount']);
            }

            $order->setTaxAmount($total['tax_amount'])
                ->setDiscountAmount(-$total['discount_amount'])
                ->setGrandTotal($total['grand_total']);

            $order->setBaseTaxAmount($total['tax_amount'])
                ->setBaseDiscountAmount(-$total['discount_amount'])
                ->setBaseGrandTotal($total['grand_total']);

            if (isset($total['subtotal'])) {
                $order->setSubtotal($total['subtotal']);
                $order->setBaseSubtotal($total['subtotal']);
            }

            if ($data['type'] === self::TYPE_COLLECT_ITEMS) {
                try {
                    $this->orderManagement->place($order);
                } catch (Exception $e) {
                    if ($this->_helperData->versionCompare('2.3.0')) {
                        $objectManager = ObjectManager::getInstance();
                        $isSalable = $objectManager->create(
                            'Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsSalableWithReservationsCondition'
                        );
                        $stockId = $this->stockItem->getStockId();
                        foreach ($order->getAllItems() as $item) {
                            $result = $isSalable->execute(
                                $item->getSku(),
                                $stockId,
                                $item->getQtyOrdered()
                            );

                            if ($result->getErrors()) { //remove items error
                                $item->delete();
                            }
                        }

                        $order->save();
                    }

                    return [
                        'error' => $e->getMessage()
                    ];
                }
            } else {
                $order->save();
            }
        }

        return [
            'success' => true
        ];
    }

    /**
     * @param Order $order
     * @param array $data
     *
     * @return array
     * @throws Exception
     */
    public function collectTotals($order, $data)
    {
        $totals = [];

        if ($data['type'] === self::TYPE_COLLECT_SHIPPING) {
            $totals = $this->collectTotalsByShipping($order, $data);
        }

        if ($data['type'] === self::TYPE_COLLECT_ITEMS) {
            $totals = $this->collectTotalsByItems($order, $data);
        }

        return $totals;
    }

    /**
     * @param Order $order
     * @param array $shipData
     *
     * @return array
     */
    public function collectTotalsByShipping($order, $shipData)
    {
        $oldShipAmount = $order->getBaseShippingAmount();
        $oldGrandTotal = $order->getBaseGrandTotal();
        $oldTaxAmount = $order->getBaseTaxAmount();
        $oldShipTax = $order->getBaseShippingTaxAmount();
        $oldDiscountAmount = $order->getBaseDiscountAmount();
        $oldShipDiscount = $order->getBaseShippingDiscountAmount();

        $newShipTaxAmount = $shipData['ship_tax_percent'] * $shipData['ship_amount'] / 100;
        $newTaxAmount = $oldTaxAmount - $oldShipTax + $newShipTaxAmount;
        $newDiscountAmount = -$oldDiscountAmount - $oldShipDiscount + $shipData['ship_discount_amount'];
        $newGrandTotal = $oldGrandTotal - $oldDiscountAmount -
                         $oldTaxAmount + $newTaxAmount - $newDiscountAmount - $oldShipAmount + $shipData['ship_amount'];

        return [
            'ship_amount'          => $shipData['ship_amount'],
            'ship_tax_amount'      => $newShipTaxAmount,
            'ship_discount_amount' => $shipData['ship_discount_amount'],
            'ship_description'     => $shipData['ship_description'],
            'tax_amount'           => $newTaxAmount,
            'discount_amount'      => $newDiscountAmount,
            'grand_total'          => $newGrandTotal
        ];
    }

    /**
     * @param Order $order
     * @param array $itemsData
     *
     * @return array
     * @throws Exception
     */
    public function collectTotalsByItems($order, $itemsData)
    {
        $oldTaxAmount = $order->getBaseTaxAmount();

        $oldItemsTaxAmount = $this->getItemsTaxAmount($order);
        $shipTax = $order->getBaseShippingTaxAmount();
        $otherTax = $oldTaxAmount - $oldItemsTaxAmount - $shipTax;
        $newTaxAmount = $itemsData['tax_amount'] + $shipTax + $otherTax;

        $oldDiscountAmount = $order->getBaseDiscountAmount();
        $oldItemsDiscountAmount = $this->getItemsDiscountAmount($order);
        $shipDiscount = $order->getBaseShippingDiscountAmount();
        $otherDiscount = -$oldDiscountAmount - $oldItemsDiscountAmount - $shipDiscount;
        $newDiscountAmount = $itemsData['discount_amount'] + $shipDiscount + $otherDiscount;

        $shipAmount = $order->getBaseShippingAmount();

        $quote = $this->quoteFactory->create()->load($this->quoteSession->getQuoteId());
        foreach ($order->getAllItems() as $item) {
            $item->delete(); //remove all old items
        }

        $order->setItems($this->quoteManagement->getResolveItems($quote)); //set new items for order item
        $newSubtotal = $this->getSubtotalByItems($order);
        $newGrandTotal = $newSubtotal + $shipAmount + $newTaxAmount - $newDiscountAmount;

        return [
            'tax_amount'      => $newTaxAmount,
            'subtotal'        => $newSubtotal,
            'discount_amount' => $newDiscountAmount,
            'grand_total'     => $newGrandTotal
        ];
    }

    /**
     * Get total discount amount all items
     *
     * @param Order $order
     *
     * @return float|null
     */
    public function getItemsDiscountAmount($order)
    {
        $discount = 0;

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $discount += $item->getBaseDiscountAmount();
        }

        return $discount;
    }

    /**
     * Get total tax amount all items
     *
     * @param Order $order
     *
     * @return float
     */
    public function getItemsTaxAmount($order)
    {
        $tax = 0;

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $tax += $item->getBaseTaxAmount();
        }

        return $tax;
    }

    /**
     * @param Order $order
     *
     * @return float
     */
    public function getSubtotalByItems($order)
    {
        $subtotal = 0;

        /** @var Item $item */
        foreach ($order->getAllVisibleItems() as $item) {
            $subtotal += $item->getBaseRowTotal();
        }

        return $subtotal;
    }
}
