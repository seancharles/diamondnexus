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

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Model\ResourceModel\Group\Collection;
use Magento\Eav\Model\Config;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageInterface;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Queue;
use Magento\Sales\Model\Order;
use Magento\Store\Model\System\Store;
use Mageplaza\EditOrder\Block\Adminhtml\Order\EditOrder;
use Mageplaza\EditOrder\Model\Config\Source\Customer;

/**
 * Class CustomerInfo
 * @package Mageplaza\EditOrder\Block\Adminhtml\Order\Edit
 */
class CustomerInfo extends Generic
{
    /**
     * @var EditOrder
     */
    protected $editOrder;

    /**
     * @var Store
     */
    protected $_systemStore;

    /**
     * @var Customer
     */
    protected $editCustomer;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var Collection
     */
    protected $_customerGroupColl;

    /**
     * @var MessageInterface
     */
    protected $_messageManager;

    /**
     * CustomerInfo constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param EditOrder $editOrder
     * @param Store $systemStore
     * @param Customer $editCustomer
     * @param Collection $customerGroupColl
     * @param Config $eavConfig
     * @param MessageInterface $messageManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        EditOrder $editOrder,
        Store $systemStore,
        Customer $editCustomer,
        Collection $customerGroupColl,
        Config $eavConfig,
        MessageInterface $messageManager,
        array $data = []
    ) {
        $this->editOrder = $editOrder;
        $this->_systemStore = $systemStore;
        $this->_customerGroupColl = $customerGroupColl;
        $this->editCustomer = $editCustomer;
        $this->eavConfig = $eavConfig;
        $this->_messageManager = $messageManager;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this|Generic
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /* @var $queue Queue */
        $order = $this->getCurrentOrder();

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'mpeditorder-customer-website-id',
            'select',
            [
                'name'     => 'order[customer][website-id]',
                'label'    => __('Associate to Website'),
                'required' => true,
                'values'   => $this->getAssociateWebsite(),
                'value'    => 1
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-email',
            'text',
            [
                'name'     => 'order[customer][email]',
                'label'    => __('Customer Email'),
                'required' => true,
                'class'    => 'validate-email',
                'value'    => $order->getCustomerEmail()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-name-prefix',
            'text',
            [
                'name'  => 'order[customer][name-prefix]',
                'label' => __('Name Prefix'),
                'value' => $order->getCustomerPrefix()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-first-name',
            'text',
            [
                'name'     => 'order[customer][first-name]',
                'label'    => __('First Name'),
                'required' => true,
                'value'    => $order->getCustomerFirstname()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-middle-name',
            'text',
            [
                'name'  => 'order[customer][middle-name]',
                'label' => __('Middle Name/Initial'),
                'value' => $order->getCustomerMiddlename()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-last-name',
            'text',
            [
                'name'     => 'order[customer][last-name]',
                'label'    => __('Last Name'),
                'required' => true,
                'value'    => $order->getCustomerLastname()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-name-suffix',
            'text',
            [
                'name'  => 'order[customer][name-suffix]',
                'label' => __('Name Suffix'),
                'value' => $order->getCustomerSuffix()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-group',
            'select',
            [
                'name'     => 'order[customer][customer-group]',
                'label'    => __('Customer Group'),
                'required' => true,
                'values'   => $this->_customerGroupColl->toOptionArray(),
                'value'    => $order->getCustomerGroupId()
            ]
        );

        $fieldset->addField('mpeditorder-customer-dob', 'date', [
            'name'        => 'order[customer][dob]',
            'label'       => __('Date of Birth'),
            'date_format' => 'M/d/yyyy',
            'timezone'    => false,
            'value'       => $order->getCustomerDob()
        ]);

        $fieldset->addField(
            'mpeditorder-customer-taxvat',
            'text',
            [
                'name'  => 'order[customer][taxvat]',
                'label' => __('Tax/VAT Number'),
                'value' => $order->getCustomerTaxvat()
            ]
        );

        $fieldset->addField(
            'mpeditorder-customer-gender',
            'select',
            [
                'name'   => 'order[customer][gender]',
                'label'  => __('Gender'),
                'values' => $this->getCustomerGenderOptions(),
                'value'  => $order->getCustomerGender() ?: 1
            ]
        );

        if (!$this->_storeManager->hasSingleStore()) {
            $fieldset->addField(
                'mpeditorder-customer-sendemail_store_id',
                'select',
                [
                    'name'   => 'order[customer][sendemail_store_id]',
                    'label'  => __('Send Welcome Email From'),
                    'values' => $this->_systemStore->getStoreValuesForForm(),
                    'value'  => 1
                ]
            );
        } else {
            $fieldset->addField(
                'mpeditorder-customer-sendemail_store_id',
                'hidden',
                [
                    'name'  => 'order[customer][sendemail_store_id]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
        }

        $fieldset->addField(
            'mpeditorder-customer-vertex',
            'text',
            [
                'name'  => 'order[customer][vertex]',
                'label' => __('Vertex Customer Code'),
            ]
        );

        $this->setForm($form);

        return $this;
    }

    /**
     * @return string
     */
    public function getButtonEditUrl()
    {
        return $this->getUrl(
            'mpeditorder/customer/form',
            [
                'order_id' => $this->getCurrentOrder()->getId(),
                'form_key' => $this->getFormKey()
            ]
        );
    }

    /**
     * @return string
     */
    public function getActionForm()
    {
        return $this->getUrl(
            'mpeditorder/customer/save',
            [
                'order_id'    => $this->getCurrentOrder()->getId(),
                'customer_id' => $this->getCurrentOrder()->getCustomerId()
            ]
        );
    }

    /**
     * @return Order Order
     */
    public function getCurrentOrder()
    {
        return $this->editOrder->getCurrentOrder();
    }

    /**
     * @return string
     */
    public function getCustomerData()
    {
        return $this->editOrder->getCustomerData();
    }

    /**
     * @return array
     */
    public function getCustomerGenderOptions()
    {
        $options = [];

        try {
            $attribute = $this->eavConfig->getAttribute('customer', 'gender');
            $options = $attribute->getSource()->getAllOptions();
            unset($options[0]);
        } catch (LocalizedException $e) {
            $this->_messageManager->addErrorMessage($e->getMessage());
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getCustomerGridUrl()
    {
        return $this->editOrder->getCustomerGridUrl();
    }

    /**
     * @return array
     */
    public function getAssociateWebsite()
    {
        $websites = $this->_storeManager->getWebsites();
        $options = [];

        foreach ($websites as $key => $value) {
            $options[] = ['label' => $value->getName(), 'value' => $key];
        }

        return $options;
    }
}
