<?php

namespace ForeverCompanies\Rules\Block\Adminhtml\Promo;

class Rule extends \Magento\Backend\Block\Widget\Grid\Container
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
