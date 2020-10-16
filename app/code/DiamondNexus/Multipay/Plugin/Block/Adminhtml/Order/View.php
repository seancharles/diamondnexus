<?php

namespace DiamondNexus\Multipay\Plugin\Block\Adminhtml\Order;

use DiamondNexus\Multipay\Block\Adminhtml\Order\AddPaymentModalBox;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package DiamondNexus\Multipay\Plugin\Block\Adminhtml\Order
 */
class View
{

    const TEMPLATE = 'DiamondNexus_Multipay::order/add_payment_modalbox.phtml';

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
                /** @var AddPaymentModalBox $blockHtml */
                $blockHtml = $subject->getLayout()->createBlock(
                    AddPaymentModalBox::class,
                    $subject->getNameInLayout() . '_modal_box_payment'
                );
                $order = $subject->getOrder();
                $customBlockHtml = $blockHtml->setData('order', $order)
                    ->setTemplate(self::TEMPLATE)
                    ->toHtml();
                return $result . $customBlockHtml;
            } catch (LocalizedException $e) {
                return $result;
            }
        }
        return $result;
    }
}
