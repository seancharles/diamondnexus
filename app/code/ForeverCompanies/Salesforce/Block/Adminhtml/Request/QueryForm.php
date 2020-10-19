<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace ForeverCompanies\Salesforce\Block\Adminhtml\Request;

/**
 * Class QueryForm
 * @package ForeverCompanies\Salesforce\Block\Adminhtml\Request
 */
class QueryForm extends \Magento\Backend\Block\Widget
{
    /**
     * QueryForm constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
}
