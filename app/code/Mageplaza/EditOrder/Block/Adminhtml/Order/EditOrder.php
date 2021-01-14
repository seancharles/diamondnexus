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

namespace Mageplaza\EditOrder\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Framework\Message\ManagerInterface as MessageInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Block\Adminhtml\Order\Create\Form;
use Magento\Sales\Block\Adminhtml\Order\View\History;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use Mageplaza\EditOrder\Helper\Data;
use Mageplaza\EditOrder\Helper\Data as HelperData;

/**
 * Class EditOrder
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order
 */
class EditOrder extends Form
{
    /**
     * @var MessageInterface
     */
    protected $_messageManager;

    /**
     * @var History
     */
    protected $orderHistory;

    /**
     * @var CollectionFactory
     */
    protected $_customerFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * EditOrder constructor.
     *
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param EncoderInterface $jsonEncoder
     * @param FormFactory $customerFormFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CurrencyInterface $localeCurrency
     * @param Mapper $addressMapper
     * @param MessageInterface $messageManager
     * @param History $orderHistory
     * @param CollectionFactory $customerFactory
     * @param Data $helperData
     * @param OrderFactory $orderFactory
     * @param QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        EncoderInterface $jsonEncoder,
        FormFactory $customerFormFactory,
        CustomerRepositoryInterface $customerRepository,
        CurrencyInterface $localeCurrency,
        Mapper $addressMapper,
        MessageInterface $messageManager,
        History $orderHistory,
        CollectionFactory $customerFactory,
        HelperData $helperData,
        OrderFactory $orderFactory,
        QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->_messageManager = $messageManager;
        $this->orderHistory = $orderHistory;
        $this->_customerFactory = $customerFactory;
        $this->_helperData = $helperData;
        $this->orderFactory = $orderFactory;
        $this->quoteFactory = $quoteFactory;

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $jsonEncoder,
            $customerFormFactory,
            $customerRepository,
            $localeCurrency,
            $addressMapper,
            $data
        );
    }

    /**
     * Override get quote session create order
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quoteId = $this->getCurrentOrder()->getQuoteId();

        return $this->quoteFactory->create()->load($quoteId);
    }

    /**
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('mpeditorder/items/loadBlock');
    }

    /**
     * @return Order
     */
    public function getCurrentOrder()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * @return string
     */
    public function getActionForm()
    {
        return $this->getUrl(
            'mpeditorder/order_edit/save',
            [
                'order_id' => $this->getRequest()->getParam('order_id'),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getEditItemsUrl()
    {
        return $this->getUrl(
            'mpeditorder/items/form',
            [
                'order_id' => $this->getCurrentOrder()->getId(),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getQuickEditUrl()
    {
        return $this->getUrl(
            'mpeditorder/order/quick',
            [
                'order_id'    => $this->getCurrentOrder()->getId(),
                'mpeditorder' => true,
                'form_key'    => $this->getFormKey()
            ]
        );
    }

    /***
     * @return string
     */
    public function getCustomerData()
    {
        $order = $this->getCurrentOrder();
        $data = [
            'webId'     => 1,
            'email'     => $order->getCustomerEmail(),
            'prefix'    => $order->getCustomerPrefix(),
            'firstName' => $order->getCustomerFirstname(),
            'midName'   => $order->getCustomerMiddlename(),
            'lastName'  => $order->getCustomerLastname(),
            'suffix'    => $order->getCustomerSuffix(),
            'groupId'   => $order->getCustomerGroupId(),
            'dob'       => $order->getCustomerDob(),
            'taxvat'    => $order->getCustomerTaxvat(),
            'gender'    => $order->getCustomerGender() ?: 1
        ];

        return Data::jsonEncode(['currentCustomer' => $data]);
    }

    /**
     * Check if product is virtual or downloadable type
     *
     * @return bool
     */
    public function isVirtualProduct()
    {
        $check = false;
        $order = $this->getCurrentOrder();
        $items = $order->getItems();
        $disAllowedTypes = [
            'virtual',
            'downloadable'
        ];

        foreach ($items as $item) {
            if (!in_array($item->getProductType(), $disAllowedTypes, true)) {
                $check = true;
            }
        }

        return $check;
    }

    /**
     * @return string
     */
    public function getCustomerGridUrl()
    {
        return $this->getUrl('mpeditorder/customer/customer', ['_current' => true]);
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->_helperData;
    }
}
