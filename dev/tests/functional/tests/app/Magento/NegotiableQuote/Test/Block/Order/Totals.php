<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Block\Order;

use Magento\Mtf\Block\Block;

/**
 * Reorder popup block on My Order page
 */
class Totals extends Block
{
    /**
     * Order totals
     *
     * @var string
     */
    protected $totals = 'tr';

    /**
     * Totals row price css selector
     *
     * @var string
     */
    protected $totalPrice = 'td .price';

    /**
     * Returns array of order totals
     *
     * @return array
     */
    public function getTotals()
    {
        $totals = [];
        $rows = $this->_rootElement->getElements($this->totals);
        foreach ($rows as $row) {
            $totals[$row->getAttribute('class')] = $row->find($this->totalPrice)->getText();
        }

        return $totals;
    }
}
