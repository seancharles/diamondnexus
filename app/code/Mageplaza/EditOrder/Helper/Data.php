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

namespace Mageplaza\EditOrder\Helper;

use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\EditOrder\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpeditorder';

    /**
     * @return bool
     */
    public function isEnabledQuickEdit()
    {
        return $this->isEnabled() && $this->getConfigGeneral('enabled_quick_edit');
    }

    /**
     * @return mixed
     */
    public function isRecalculateShippingFee()
    {
        return $this->getConfigGeneral('auto_recalculate_shipping_fee');
    }

    /**
     * get array different between 2 array
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public function arrayDifferent($array1, $array2)
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2)) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $multidimensionalDiff = $this->arrayDifferent($value, $array2[$key]);
                    if (count($multidimensionalDiff) > 0) {
                        $difference[$key] = $multidimensionalDiff;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = $value;
                }
            }
        }

        return $difference;
    }

    /**
     * Get Edited type for manage logs
     *
     * @param array $diffData
     *
     * @return string
     */
    public function getEditedType($diffData)
    {
        if (isset($diffData['order'])) {
            if (isset($diffData['items']) ||
                count($diffData['order']) > 1 ||
                (count($diffData['order']) === 1 && isset($diffData['payment'])) ||
                (!isset($diffData['order']['shipping_method']) &&
                 count($diffData['order']) === 1 && isset($diffData['method_detail']))
            ) {
                return __('Quick Edit (all)');
            }
        }

        if (isset($diffData['method_detail']) && !isset($diffData['order']) && count($diffData) > 1) {
            return __('Quick Edit (all)');
        }

        $keys = $this->arrayKeysMulti($diffData);

        if (in_array('info', $keys, true)) {
            return __('Order Information');
        }

        if (in_array('customer', $keys, true)) {
            return __('Customer Information');
        }

        if (in_array('billing_address', $keys, true)) {
            return __('Billing Address');
        }

        if (in_array('shipping_address', $keys, true)) {
            return __('Shipping Address');
        }

        if (in_array('method_detail', $keys, true) || in_array('shipping_method', $keys, true)) {
            return __('Shipping Method');
        }

        if (in_array('payment', $keys, true)) {
            return __('Payment Method');
        }

        if (in_array('item', $keys, true)) {
            return __('Items');
        }

        return '';
    }

    /**
     * Get all key array
     *
     * @param array $data
     *
     * @return array
     */
    public function arrayKeysMulti($data)
    {
        $keys = [];
        $childKey = [];

        foreach ($data as $key => $value) {
            $keys[] = $key;

            if (is_array($value)) {
                $childKey = $this->arrayKeysMulti($value);
            }
        }

        return array_merge($keys, $childKey);
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public function isEdited($array)
    {
        $isEdited = false;
        foreach ($array as $key => $value) {
            if ($key === 'payment' && isset($array[$key])) {
                foreach ($array[$key] as $p_key => $p_value) {
                    if ($array[$key][$p_key]) {
                        $isEdited = true;
                        break;
                    }
                }
            } elseif ($array[$key]) {
                $isEdited = true;
                break;
            }
        }

        return $isEdited;
    }
}
