<?php


namespace ForeverCompanies\CustomApi\Plugin\Adminhtml\Order;

use ForeverCompanies\CustomApi\Block\Adminhtml\Order\AsdAndDeliveryModalBox;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class View
 * @package ForeverCompanies\CustomApi\Plugin\Adminhtml\Order
 */
class View
{

    public function beforeSetLayout(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $layout
    ) {
        $subject->addButton(
            'sendordersms',
            [
                'label' => __('Modify ASD & Delivery Date'),
                'onclick' => "",
                'class' => 'action-default action-warranty-order',
            ]
        );
        return [$layout];
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     * @param $result
     * @return false|string
     */
    public function afterToHtml(
        \Magento\Sales\Block\Adminhtml\Order\View $subject,
        $result
    ) {
        if ($subject->getNameInLayout() == 'sales_order_edit') {
            try {
                /** @var AsdAndDeliveryModalBox $customBlockHtml */
                $customBlockHtml = $subject->getLayout()->createBlock(
                    AsdAndDeliveryModalBox::class,
                    $subject->getNameInLayout() . '_modal_box'
                );
                $customBlockHtml->setData('order', $subject->getOrder())
                    ->setTemplate('ForeverCompanies_CustomApi::order/modalbox.phtml');
                return $result . $customBlockHtml->toHtml();
            } catch (LocalizedException $e) {
                return false;
            }
        }
        return $result;
    }
}
