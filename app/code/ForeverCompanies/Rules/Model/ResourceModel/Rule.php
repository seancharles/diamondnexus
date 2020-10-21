<?php

namespace ForeverCompanies\Rules\Model\ResourceModel;

class Rule extends \Magento\Rule\Model\ResourceModel\AbstractResource
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
