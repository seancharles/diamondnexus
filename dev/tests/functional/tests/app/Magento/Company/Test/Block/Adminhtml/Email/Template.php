<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Email;

use Magento\Mtf\Block\Block;

/**
 * Form for creation of the company.
 */
class Template extends Block
{
    /**
     * Css locator for load template button.
     *
     * @var string
     */
    protected $loadTemplateButton = '[data-ui-id="template-edit-load-button"]';

    /**
     * Css locator for template.
     *
     * @var string
     */
    protected $selectTemplate = '#template_select';

    /**
     * Css locator for "Assign Company Admin" template.
     *
     * @var string
     */
    protected $selectTemplateOption = '#template_select [value="company_email_customer_assign_super_user_template"]';

    /**
     * Load template
     *
     * @return void
     */
    public function loadTemplate()
    {
        $this->_rootElement->find($this->selectTemplate)->click();
        $this->_rootElement->find($this->selectTemplateOption)->click();
        $this->_rootElement->find($this->loadTemplateButton)->click();
    }
}
