<?php

namespace ForeverCompanies\Rules\Controller\Adminhtml\Promo\Rule;

use ForeverCompanies\Rules\Controller\Adminhtml\Promo\Rule;

class NewAction extends Rule
{
    /**
     * New action
     *
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
