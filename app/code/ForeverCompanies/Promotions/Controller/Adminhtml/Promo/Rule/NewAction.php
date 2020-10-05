<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule;

class NewAction extends \ForeverCompanies\Promotions\Controller\Adminhtml\Promo\Rule
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'ForeverCompanies_Promotions::promotions';

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
