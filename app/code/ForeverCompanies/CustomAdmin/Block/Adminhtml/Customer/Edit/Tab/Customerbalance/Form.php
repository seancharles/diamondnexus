<?php
namespace ForeverCompanies\CustomAdmin\Block\Adminhtml\Customer\Edit\Tab\Customerbalance;

class Form extends \Magento\CustomerBalance\Block\Adminhtml\Customer\Edit\Tab\Customerbalance\Form
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authorization;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\AuthorizationInterface $authorization,
        array $data = []
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_systemStore = $systemStore;
        $this->_authorization = $authorization;
        parent::__construct($context, $registry, $formFactory, $customerFactory, $systemStore, $data);
    }
    
    /**
     * Prepare form fields
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $prefix = '_customerbalance';
        $form->setHtmlIdPrefix($prefix);
        $form->setFieldNameSuffix('customerbalance');

        /** @var Customer $customer */
        $customer = $this->_customerFactory->create()->load($this->getRequest()->getParam('id'));

        // Custom ACL for creating and modifying store credit
        if($this->_authorization->isAllowed('ForeverCompanies_CustomAdmin::issuecredit') === true) {

            /** @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
            $fieldset = $form->addFieldset('storecredit_fieldset', ['legend' => __('Update Balance')]);

            if (!$this->_storeManager->isSingleStoreMode()) {
                $fieldset->addField(
                    'website_id',
                    'select',
                    [
                        'name' => 'website_id',
                        'label' => __('Website'),
                        'title' => __('Website'),
                        'values' => $this->_systemStore->getWebsiteValuesForForm(),
                        'onchange' => 'updateEmailWebsites()',
                        'data-form-part' => $this->getData('target_form')
                    ]
                );
            }
            $fieldset->addField(
                'amount_delta',
                'text',
                [
                    'name' => 'amount_delta',
                    'label' => __('Update Balance'),
                    'title' => __('Update Balance'),
                    'comment' => __('An amount on which to change the balance'),
                    'data-form-part' => $this->getData('target_form')
                ]
            );
            $fieldset->addField(
                'notify_by_email',
                'checkbox',
                [
                    'name' => 'notify_by_email',
                    'label' => __('Notify Customer by Email'),
                    'title' => __('Notify Customer by Email'),
                    'data-form-part' => $this->getData('target_form'),
                    'after_element_html' => !$this->_storeManager
                        ->isSingleStoreMode() ? '<script>require(["prototype"], function(){' .
                        "\n                \$('{$prefix}notify_by_email').disableSendemail = function() {"
                        ."\n                    \$('{$prefix}store_id').disabled = (this.checked) ? false : true;"
                        ."\n                }.bind(\$('{$prefix}notify_by_email'));"
                        ."\n                Event.observe('{$prefix}notify_by_email', 'click',"
                        ." \$('{$prefix}notify_by_email').disableSendemail);"
                        ."\n                \$('{$prefix}notify_by_email').disableSendemail();\n                " .
                        '});</script>' : ''
                ]
            );
            if (!$this->_storeManager->isSingleStoreMode()) {
                $field = $fieldset->addField(
                    'store_id',
                    'select',
                    [
                        'name' => 'store_id',
                        'label' => __('Send Email Notification From the Following Store View'),
                        'title' => __('Send Email Notification From the Following Store View'),
                        'data-form-part' => $this->getData('target_form')
                    ]
                );
                $renderer = $this->getLayout()->createBlock(
                    \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
                );
                $field->setRenderer($renderer);
            }
            $fieldset->addField(
                'comment',
                'text',
                [
                    'name' => 'comment',
                    'label' => __('Comment'),
                    'title' => __('Comment'),
                    'comment' => __('Comment'),
                    'data-form-part' => $this->getData('target_form')
                ]
            );
        } else {
            /** @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
            $fieldset = $form->addFieldset('storecredit_fieldset', ['legend' => __('You do not have permission to edit store credit entries, please ask a manager for assistance.')]);
        }
        
        if ($customer->isReadonly()) {
            if ($form->getElement('website_id')) {
                $form->getElement('website_id')->setReadonly(true, true);
            }
            $form->getElement('store_id')->setReadonly(true, true);
            $form->getElement('amount_delta')->setReadonly(true, true);
            $form->getElement('notify_by_email')->setReadonly(true, true);
        }
        
        $form->setValues($customer->getData());
        $this->updateFromSession($form, $customer->getId());
        $this->setForm($form);
        return $this;
    }
}