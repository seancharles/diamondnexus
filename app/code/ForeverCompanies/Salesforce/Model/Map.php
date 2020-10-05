<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Map
 *
 * @package ForeverCompanies\Salesforce\Model
 *
 * @method Map setStatus(int $status)
 */
class Map extends AbstractModel
{
    /**
     * Initialize resources
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ForeverCompanies\Salesforce');
    }

    /**
     * Salesforce
     *
     * @return mixed
     */
    public function getSalesforce()
    {
        return $this->getData('salesforce');
    }

    /**
     * Magento
     *
     * @return mixed
     */
    public function getMagento()
    {
        return $this->getData('magento');
    }
}
