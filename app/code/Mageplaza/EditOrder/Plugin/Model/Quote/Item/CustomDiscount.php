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

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class CustomDiscount
 * @package Mageplaza\EditOrder\Plugin\Model
 */
class CustomDiscount
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
     * CustomDiscount constructor.
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
     * @param Validator $object
     * @param Closure $process
     * @param AbstractItem $item
     *
     * @return $this|mixed
     */
    public function aroundProcess(Validator $object, Closure $process, $item)
    {
        if (!$this->_helperData->isEnabled()) {
            return $process($item);
        }
        $process($item);
        $request = $this->_request->getParams();

        if (isset($request['item'])) {
            foreach ($request['item'] as $itemId => $data) {
                if ($itemId === (int) $item->getId()) {
                    if (isset($data['use_custom_discount'])) {
                        $type = $data['discount_type'];
                        $value = $data['discount_value'] ?: 0;
                        $itemPrice = $object->getItemPrice($item);
                        if ($itemPrice < 0) {
                            return $process($item);
                        }
                        $discountAmount = $type === 'fixed' ? $value : ($itemPrice * $value * $item->getQty()) / 100;
                        $discountPercent = $type === 'percent' ? $value : ($value * 100) / $itemPrice;
                        $item->setDiscountAmount($discountAmount);
                        $item->setBaseDiscountAmount($discountAmount);
                        $item->setDiscountPercent($discountPercent);
                        $item->setMpCustomDiscountType($type);
                        $item->setMpCustomDiscountValue($value);
                    } else {
                        $item->setMpCustomDiscountType(0);
                        $item->setMpCustomDiscountValue(0);
                    }
                }
            }
        }

        return $this;
    }
}
