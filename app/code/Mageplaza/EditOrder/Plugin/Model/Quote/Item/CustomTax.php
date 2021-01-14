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

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Tax\Api\Data\TaxDetailsItemInterface;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CustomTax
 * @package Mageplaza\EditOrder\Plugin\Model\Quote\Item
 */
class CustomTax
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * CustomTax constructor.
     *
     * @param RequestInterface $request
     * @param HelperData $helperData
     */
    public function __construct(
        RequestInterface $request,
        HelperData $helperData
    ) {
        $this->_request = $request;
        $this->_helperData = $helperData;
    }

    /**
     * @param CommonTaxCollector $subject
     * @param CommonTaxCollector $result
     * @param AbstractItem $quoteItem
     * @param TaxDetailsItemInterface $itemTaxDetails
     * @param TaxDetailsItemInterface $baseItemTaxDetails
     * @param Store $store
     *
     * @return mixed
     * @SuppressWarnings(Unused)
     */
    public function afterUpdateItemTaxInfo(
        CommonTaxCollector $subject,
        $result,
        $quoteItem,
        $itemTaxDetails,
        $baseItemTaxDetails,
        $store
    ) {
        if (!$this->_helperData->isEnabled()) {
            return $result;
        }

        $request = $this->_request->getParams();
        if (isset($request['item'])) {
            foreach ($request['item'] as $itemId => $data) {
                if (!empty($data['tax']) && ($itemId === (int) $quoteItem->getId())) {
                    $itemPriceWithoutDiscount = $quoteItem->getRowTotal() - $quoteItem->getTotalDiscountAmount();
                    $taxPercent = $data['tax'] ?: 0;
                    $taxAmount = $itemPriceWithoutDiscount * $taxPercent / 100;
                    $quoteItem->setTaxPercent($taxPercent);
                    $quoteItem->setTaxAmount($taxAmount);
                    $quoteItem->setBaseTaxAmount($taxAmount);
                    $quoteItem->setMpCustomTaxPercent($taxPercent);
                }
            }
        }

        return $result;
    }
}
