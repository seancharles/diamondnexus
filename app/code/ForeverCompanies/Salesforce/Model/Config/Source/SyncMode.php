<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Model\Config\Source;


/**
 * Class SyncMode
 *
 * @package ForeverCompanies\Salesforce\Model\ResourceModel
 */
class SyncMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options = [ 1 => 'Add to Queue', 2 => 'Auto Sync'];

    /**
     * Return options array
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_options;
        return $options;
    }
}
