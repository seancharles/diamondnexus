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

use Magento\Sales\Block\Adminhtml\Order\Create\Form;

/**
 * Class OrderItems
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class OrderItems extends Form
{
    /**
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('mpeditorder/items/loadBlock');
    }
}
