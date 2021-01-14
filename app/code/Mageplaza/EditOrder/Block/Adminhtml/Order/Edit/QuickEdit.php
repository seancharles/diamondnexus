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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit;

use Mageplaza\EditOrder\Block\Adminhtml\Order\EditOrder;

/**
 * Class QuickEdit
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class QuickEdit extends EditOrder
{
    /**
     * @return array
     */
    public function getTabs()
    {
        $data = [
            'info'            => [
                'id'    => 'mpeditorder-order-info',
                'label' => __('Edit Order Information'),
                'block' => 'mpeditorder.order.info'
            ],
            'customer'        => [
                'id'    => 'mpeditorder-account-info',
                'label' => __('Edit Customer Information'),
                'block' => 'mpeditorder.customer'
            ],
            'billing_address' => [
                'id'    => 'mpeditorder-billing-address',
                'label' => __('Edit Billing Address'),
                'block' => 'mpeditorder.billing.address'
            ],

            'payment_method' => [
                'id'    => 'mpeditorder-payment-method',
                'label' => __('Edit Payment Method'),
                'block' => 'mpeditorder.payment.method'
            ],
        ];

        if ($this->isVirtualProduct()) {
            $data['shipping_address'] = [
                'id'    => 'mpeditorder-shipping-address',
                'label' => __('Edit Shipping Address'),
                'block' => 'mpeditorder.shipping.address'
            ];

            $data['shipping_method'] = [
                'id'    => 'mpeditorder-shipping-method',
                'label' => __('Edit Shipping Method'),
                'block' => 'mpeditorder.shipping.method'
            ];
        }

        $data['items'] = [
            'id'    => 'mpeditorder-items',
            'label' => __('Edit Items Ordered'),
            'block' => 'mpeditorder.items'
        ];

        return $data;
    }
}
