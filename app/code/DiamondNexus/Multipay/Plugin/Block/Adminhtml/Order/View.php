<?php

namespace DiamondNexus\Multipay\Plugin\Block\Adminhtml\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package DiamondNexus\Multipay\Plugin\Block\Adminhtml\Order
 */
class View
{

    public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $subject->addButton(
            'add_payment_button',
            [
                'label' => __('Add payment'),
                'onclick' => "",
                'class' => 'action-default action-warranty-order',
            ]
        );
        return [$layout];
    }

    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if ($subject->getNameInLayout() == 'sales_order_edit') {
            try {
                $customBlockHtml = $subject->getLayout()->createBlock(
                    \DiamondNexus\Multipay\Block\Adminhtml\Order\AddPaymentModalBox::class,
                    $subject->getNameInLayout() . '_modal_box_payment'
                )->setOrder($subject->getOrder())
                    ->setTemplate('DiamondNexus_Multipay::order/add_payment_modalbox.phtml')
                    ->toHtml();
                return $result . $customBlockHtml;
            } catch (LocalizedException $e) {
                return $result;
            }
        }
        return $result;
    }
}
