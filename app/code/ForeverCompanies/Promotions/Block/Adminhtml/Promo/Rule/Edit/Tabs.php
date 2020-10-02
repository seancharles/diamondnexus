<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Block\Adminhtml\Promo\Rule\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('rules_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('ForeverCompanies Catalog Price Rules'));
    }
}
