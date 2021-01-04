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

namespace Mageplaza\EditOrder\Block\Adminhtml\Logs;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Button
 * @package Mageplaza\EditOrder\Block\Adminhtml\Logs
 */
class Button extends Container
{
    /**
     * Initialize Rule edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Mageplaza_EditOrder';
        $this->_controller = 'adminhtml_logs';

        parent::_construct();

        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
    }
}
