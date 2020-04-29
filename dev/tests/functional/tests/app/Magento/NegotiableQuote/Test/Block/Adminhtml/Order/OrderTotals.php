<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Adminhtml\Order;

use Magento\Sales\Test\Block\Adminhtml\Order\Totals;
use Magento\Mtf\Client\Locator;

/**
 * Order totals block on Order page.
 */
class OrderTotals extends Totals
{
    /**
     * Order totals price row selector.
     *
     * @var string
     */
    protected $totals = '.data-table>tbody>tr';

    /**
     * Order totals price selector in price row.
     *
     * @var string
     */
    protected $totalPrice = '.price';

    /**
     * "Catalog Price Excl. Tax" row xpath selector.
     *
     * @var string
     */
    private $catalogTotalPriceExclTax = '//tr[normalize-space(td)="Catalog Total Price (Excl. Tax)"]//span';

    /**
     * "Catalog Total Price (Incl. Tax)" row xpath selector.
     * @var string
     */
    private $catalogTotalPriceInclTax = '//tr[normalize-space(td)="Catalog Total Price (Incl. Tax)"]//span';

    /**
     * "Negotiated Discount" row xpath selector.
     *
     * @var string
     */
    private $negotiatedDiscount = '//tr[normalize-space(td)="Negotiated Discount"]//span';

    /**
     * Returns array of quote totals.
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [
            'catalog_price_excl_tax' => $this->getCatalogTotalPriceExclTax(),
            'catalog_price_incl_tax' => $this->getCatalogTotalPriceInclTax(),
            'order_subtotal_excl_tax' => $this->getSubtotalExclTax(),
            'order_subtotal_incl_tax' => $this->getSubtotalInclTax(),
            'negotiated_discount' => $this->getNegotiatedDiscount(),
            'discount' => $this->getDiscount()
        ];

        return $totals;
    }

    /**
     * Get CatalogTotalPriceExclTax text.
     *
     * @return string
     */
    public function getCatalogTotalPriceExclTax(): string
    {
        $catalogTotalPriceExclTax = $this->_rootElement->find($this->catalogTotalPriceExclTax, Locator::SELECTOR_XPATH)
            ->getText();
        return (string)$this->escapeCurrency($catalogTotalPriceExclTax);
    }

    /**
     * Get CatalogTotalPriceInclTax text.
     *
     * @return string
     */
    public function getCatalogTotalPriceInclTax(): string
    {
        $catalogTotalPriceInclTax = $this->_rootElement->find($this->catalogTotalPriceInclTax, Locator::SELECTOR_XPATH)
            ->getText();
        return (string)$this->escapeCurrency($catalogTotalPriceInclTax);
    }

    /**
     * Get NegotiatedDiscount text.
     *
     * @return string
     */
    public function getNegotiatedDiscount(): string
    {
        $negotiatedDiscount = $this->_rootElement->find($this->negotiatedDiscount, Locator::SELECTOR_XPATH)->getText();
        return (string)$this->escapeCurrency($negotiatedDiscount);
    }

    /**
     * Returns array of quote totals when display and base currencies differ.
     *
     * @return array
     */
    public function getTotalsWithDifferentCurrencies()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            $prices = $row->getElements($this->totalPrice);
            if (count($prices)) {
                foreach ($prices as $total) {
                    $totals[$row->getAttribute('class')][] = $total->getText();
                }
            }
        }

        return $totals;
    }
}
