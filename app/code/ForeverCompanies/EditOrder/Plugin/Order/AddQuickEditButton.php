<?php

namespace ForeverCompanies\EditOrder\Plugin\Order;

use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View;

/**
 * Class AddQuickEditButton
 * @package Mageplaza\EditOrder\Plugin\Order
 */
class AddQuickEditButton extends \Mageplaza\EditOrder\Plugin\Order\AddQuickEditButton
{

    /**
     * @param View $object
     * @param LayoutInterface $layout
     *
     * @return array
     */
    public function beforeSetLayout(View $object, LayoutInterface $layout)
    {
        if (!$this->_helperData->isEnabledQuickEdit() ||
            $object->getOrder()->getStatus() !== 'quote') {
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
