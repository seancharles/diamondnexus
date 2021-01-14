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

namespace Mageplaza\EditOrder\Observer\Adminhtml;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class ConvertToQuoteItem
 * @package Mageplaza\EditOrder\Observer\Adminhtml
 */
class ConvertToQuoteItem implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ConvertToQuoteItem constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isEnabled()) {
            return;
        }

        $orderItem = $observer->getData('order_item');
        $quoteItem = $observer->getData('quote_item');
        $quoteItem->setMpCustomTaxPercent($orderItem->getMpCustomTaxPercent());
        $quoteItem->setMpCustomDiscountType($orderItem->getMpCustomDiscountType());
        $quoteItem->setMpCustomDiscountValue($orderItem->getMpCustomDiscountValue());
    }
}
