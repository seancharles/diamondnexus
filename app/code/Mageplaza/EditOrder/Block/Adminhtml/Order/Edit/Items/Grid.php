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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items;

use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Block\Adminhtml\Order\Create\Items\Grid as GridItems;

/**
 * Class Grid
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Items
 */
class Grid extends GridItems
{
    /**
     * Check to update form data
     *
     * @return bool
     */
    public function isUpdate()
    {
        $request = $this->getRequest()->getParams();

        if (isset($request['order_id']) && $request['order_id']) { // if click edit item button
            return true;
        }

        if (isset($request['block'])) { // if click add product button
            $params = explode(',', $request['block']);

            return in_array('search', $params, true);
        }

        return false;
    }

    /**
     * Get Total tax quote items
     *
     * @param $items
     *
     * @return float
     */
    public function getTotalTaxItems($items)
    {
        $tax = 0;
        /** @var Item $item */
        foreach ($items as $item) {
            $tax += $item->getTaxAmount();
        }

        return $tax;
    }

    /**
     * Check is custom discount
     *
     * @param Item $item
     *
     * @return bool
     */
    public function isCustomDiscount($item)
    {
        if ($item->getNoDiscount()) {
            return false;
        }

        return $item->getMpCustomDiscountValue();
    }

    /**
     * @return array
     */
    public function getDiscountOptions()
    {
        return [
            'percent' => __('Percent'),
            'fixed'   => __('Fixed Amount')
        ];
    }
}
