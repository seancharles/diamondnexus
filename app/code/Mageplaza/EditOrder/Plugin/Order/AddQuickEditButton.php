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
 * @category  Mageplaza
 * @package   Mageplaza_EditOrder
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\EditOrder\Plugin\Order;

use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class AddQuickEditButton
 * @package Mageplaza\EditOrder\Plugin\Order
 */
class AddQuickEditButton
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * AddQuickEditButton constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    /**
     * @param View $object
     * @param LayoutInterface $layout
     *
     * @return array
     */
    public function beforeSetLayout(View $object, LayoutInterface $layout)
    {
        if (!$this->_helperData->isEnabledQuickEdit() ||
            $object->getOrder()->getState() !== 'new') {
            return [$layout];
        }

        $object->addButton('mpeditorder_quick_edit', [
            'label' => __('Quick Edit'),
            'class' => 'mpeditorder_quick_edit-button',
            'id'    => 'mpeditorder-quick-edit-button',
        ]);

        return [$layout];
    }
}
