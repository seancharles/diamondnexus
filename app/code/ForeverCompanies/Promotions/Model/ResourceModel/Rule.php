<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Promotions\Model\ResourceModel;

class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
{
    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('forevercompanies_promotions', 'rule_id');
    }
}
