<?php

namespace ForeverCompanies\Rules\Controller\Adminhtml\Promo\Rule;

class Index extends \ForeverCompanies\Rules\Controller\Adminhtml\Promo\Rule
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('ForeverCompanies Catalog Rules'), __('ForeverCompanies Catalog  Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ForeverCompanies Catalog  Rules'));
        $this->_view->renderLayout('root');
    }
}
