<?php

namespace ForeverCompanies\AssignCustomerToOrder\Plugin\Block\Adminhtml\Order;

use ForeverCompanies\AssignCustomerToOrder\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View as OrigView;

/**
 * Class View
 * @package ForeverCompanies\AssignCustomerToOrder\Plugin\Block\Adminhtml\Order
 */
class View
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * View constructor.
     * @param Data $helperData
     * @param UrlInterface $urlBuilder
     */
    public function __construct(Data $helperData, UrlInterface $urlBuilder)
    {
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param OrigView $object
     * @param LayoutInterface $layout
     *
     * @return array
     */
    public function beforeSetLayout(OrigView $object, LayoutInterface $layout)
    {
        $order = $object->getOrder();
        if (!$this->helperData->isEnabled($order)) {
            return [$layout];
        }
        $message ='Are you sure you want to do this?';
        $url = $this->urlBuilder->getUrl('AssignCustomerToOrder/customer/index');

        $object->addButton('assign_customer_to_order_btn', [
            'label' => __('Assign Customer To Order'),
            'class' => 'assign_customer_to_order_btn-button',
            'id'    => 'assign-customer-to-order-btn',
            //'onclick' => "confirmSetLocation('{$message}', '{$url}')"
            'onclick' => "guestToCustomerButtonClick('{$url}', '{$order->getId()}', '{$message}')",
        ]);

        return [$layout];
    }
}
