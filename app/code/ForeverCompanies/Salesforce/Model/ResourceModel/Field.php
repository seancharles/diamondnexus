<?php


namespace ForeverCompanies\Salesforce\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Field
 *
 * @package Magenest\Salesforce\Model\ResourceModel
 */
class Field extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('forevercompanies_salesforce_field','id');
    }
}
