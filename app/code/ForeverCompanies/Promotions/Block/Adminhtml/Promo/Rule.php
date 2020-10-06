<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Block\Adminhtml\Promo;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ForeverCompanies_Promotions::promotions';

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'promo_rule';
        $this->_headerText = __('ForeverCompanies Catalog Price Rules');
        $this->_addButtonLabel = __('Add ForeverCompanies Catalog Price Rules');
        parent::_construct();
    }
}
