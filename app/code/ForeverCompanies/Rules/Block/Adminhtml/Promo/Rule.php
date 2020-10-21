<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace ForeverCompanies\Rules\Block\Adminhtml\Promo;

use Magento\Backend\Block\Widget\Grid\Container;

class Rule extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'promo_rule';
        $this->_headerText = __('Forever Companies Rules');
        $this->_addButtonLabel = __('Add New Rule');
        parent::_construct();
    }
}
