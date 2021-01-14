<?php

namespace ForeverCompanies\Salesforce\Block\Adminhtml\Map;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

/**
 * Class UpdateFields
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Map
 */
class UpdateFields extends Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get URL
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('salesforce/field/retrieve', ['_current' => false]);
    }

    /**
     * Get URL
     *
     * @return string
     */
    public function getUpdateAllFields()
    {
        return $this->getUrl('salesforce/field/updateAllFields', ['_current' => false]);
    }
}
