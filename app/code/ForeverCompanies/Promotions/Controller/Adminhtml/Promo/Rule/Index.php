<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule;

class Index extends \ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule
{
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('ForeverCompanies Catalog Price Rules'), __('ForeverCompanies Catalog Price Rules'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('ForeverCompanies Catalog Price Rules'));
        $this->_view->renderLayout('root');
    }
}
