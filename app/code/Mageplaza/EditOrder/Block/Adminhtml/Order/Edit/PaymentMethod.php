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
 * Class PaymentMethod
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class PaymentMethod extends EditOrder
{
    /**
     * @return string
     */
    public function getActionForm()
    {
        return $this->getUrl(
            'mpeditorder/payment_method/save',
            [
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getEditPaymentButtonUrl()
    {
        return $this->getUrl(
            'mpeditorder/payment_method/listMethod',
            [
                'order_id'    => $this->getRequest()->getParam('order_id'),
                'mpeditorder' => true,
                'form_key'    => $this->getFormKey()
            ]
        );
    }
}
