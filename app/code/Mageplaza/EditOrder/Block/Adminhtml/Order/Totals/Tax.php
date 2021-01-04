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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Totals;

use Magento\Framework\DataObject;
use Magento\Sales\Block\Adminhtml\Order\Totals\Tax as TotalsTax;
use Magento\Sales\Model\Order;

/**
 * Class ShippingMethod
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class Tax extends TotalsTax
{
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->getCurrentOrder();
    }

    /**
     * @return DataObject
     */
    public function getSource()
    {
        return $this->getCurrentOrder();
    }

    /**
     * Add tax total string
     *
     * @param string $after
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     */
    protected function _addTax($after = 'discount')
    {
        $taxTotal = new DataObject(['code' => 'tax', 'block_name' => 'tax']);
        $this->getParentBlock()->addTotal($taxTotal, $after);

        return $this;
    }
}
