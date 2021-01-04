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

use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Address;
use Magento\Sales\Model\Order;
use Mageplaza\EditOrder\Block\Adminhtml\Order\EditOrder;

/**
 * Class ShippingAddress
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class ShippingAddress extends Address
{
    /**
     * @return mixed
     */
    public function getHelperData()
    {
        /** @var EditOrder $parentBlock */
        $parentBlock = $this->getParentBlock();

        return $parentBlock->getHelperData();
    }

    /**
     * @return Order
     */
    public function getCurrentOrder()
    {
        return $this->getOrder();
    }

    /**
     * @return array|OrderAddressInterface|null
     */
    public function getFormValues()
    {
        return $this->getCurrentOrder()->getShippingAddress();
    }

    /**
     * @return string
     */
    public function getShippingAddressEditUrl()
    {
        return $this->getUrl(
            'mpeditorder/address/form',
            [
                'type'     => 'shipping',
                'order_id' => $this->getRequest()->getParam('order_id'),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return 'shipping_address';
    }

    /**
     * @return bool|int|mixed
     */
    public function getAddressId()
    {
        return $this->getCurrentOrder()->getShippingAddress() ?
            $this->getCurrentOrder()->getShippingAddress()->getId() : 0;
    }
}
