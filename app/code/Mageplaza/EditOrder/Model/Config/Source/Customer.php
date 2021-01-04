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

namespace Mageplaza\EditOrder\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Customer
 * @package Mageplaza\EditOrder\Model\Config\Source
 */
class Customer implements ArrayInterface
{
    const EDIT_CUSTOMER_TYPE   = 'edit';
    const CHANGE_CUSTOMER_TYPE = 'change';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::EDIT_CUSTOMER_TYPE, 'label' => __('Edit Current Customer')],
            ['value' => self::CHANGE_CUSTOMER_TYPE, 'label' => __('Change Customer')],
        ];
    }
}
