<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace ForeverCompanies\Rules\Block\Adminhtml\Promo\Rule\Edit;

use Magento\Backend\Block\Widget\Form\Generic;


class Form extends Generic
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('promo_rule_form');
        $this->setTitle(__('Rule Information'));
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('forevercompanies_rules/promo_rule/save'),
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
