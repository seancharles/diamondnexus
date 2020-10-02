<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'forevercompanies_rules';
        $this->_headerText = __('ForeverCompanies Catalog Price Rules');
        $this->_addButtonLabel = __('Add ForeverCompanies Catalog Price Rules');
        parent::_construct();
    }
}
