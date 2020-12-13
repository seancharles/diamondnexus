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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment;

use Magento\Sales\Block\Adminhtml\Order\Create\Billing\Method\Form;

/**
 * Class ListPayment
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit\Payment
 */
class ListPayment extends Form
{
    /**
     * Set default option payment method
     *
     * @return bool|false|string
     */
    public function getSelectedMethodCode()
    {
        $methods = $this->getMethods();
        if (count($methods) === 1) {
            $methodCode = '';
            foreach ($methods as $method) {
                $methodCode = $method->getCode();
            }

            return $methodCode;
        }

        $currentMethodCode = $this->getPaymentCode();
        if ($currentMethodCode) {
            return $currentMethodCode;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function _canUseMethod($method)
    {
        if ($this->getOfflineOnly() && !$method->isOffline()) {
            return false;
        }

        return parent::_canUseMethod($method);
    }
}
