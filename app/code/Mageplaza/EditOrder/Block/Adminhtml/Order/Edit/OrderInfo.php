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

use Magento\Sales\Model\Order;
use Mageplaza\EditOrder\Block\Adminhtml\Order\EditOrder;

/**
 * Class OrderInfo
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class OrderInfo extends EditOrder
{
    /**
     * @return Order
     */
    public function getTemplateOrder()
    {
        return $this->getOrder();
    }

    /**
     * @return string
     */
    public function getActionForm()
    {
        return $this->getUrl(
            'mpeditorder/order_info/save',
            [
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getButtonEditUrl()
    {
        return $this->getUrl(
            'mpeditorder/order_info/form',
            [
                'order_id' => $this->getCurrentOrder()->getId(),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * Get Status of state order
     *
     * @return array
     */
    public function getStatuses()
    {
        $state = $this->getCurrentOrder()->getState();

        return $this->getCurrentOrder()->getConfig()->getStateStatuses($state);
    }
}
