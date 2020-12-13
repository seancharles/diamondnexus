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

namespace Mageplaza\EditOrder\Block\Adminhtml\Logs\Order;

use Mageplaza\EditOrder\Block\Adminhtml\Logs\View;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class Total
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs\Order
 */
class Total extends View
{
    /**
     * @return array
     */
    public function getOldTotalData()
    {
        return HelperData::jsonDecode($this->getLog()->getOldTotalData());
    }
}
