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
 * @category  Mageplaza
 * @package   Mageplaza_EditOrder
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Plugin\Model\Quote\Item;

use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\ToOrderItem as QuoteItemToOrderItem;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class ToOrderItem
 * @package Mageplaza\EditOrder\Plugin\Model\Quote\Item
 */
class ToOrderItem
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * ToOrderItem constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param QuoteItemToOrderItem $subject
     * @param $result
     * @param Item|AddressItem $item
     * @param array $data
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function afterConvert(QuoteItemToOrderItem $subject, $result, $item, $data = [])
    {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }

        $result->setOriginalPrice($item->getProduct()->getPrice());
        $result->setBaseOriginalPrice($item->getProduct()->getPrice());
        $result->setMpCustomTaxPercent($item->getMpCustomTaxPercent());
        $result->setMpCustomDiscountType($item->getMpCustomDiscountType());
        $result->setMpCustomDiscountValue($item->getMpCustomDiscountValue());

        return $result;
    }
}
