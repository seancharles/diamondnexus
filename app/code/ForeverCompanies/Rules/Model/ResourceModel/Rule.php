<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ForeverCompanies\Rules\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;


class Rule extends AbstractResource
{

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('forevercompanies_rules', 'rule_id');
    }
}
