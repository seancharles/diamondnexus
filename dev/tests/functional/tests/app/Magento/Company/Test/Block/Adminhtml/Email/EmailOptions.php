<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Email;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Form for email options.
 */
class EmailOptions extends Block
{
    /**
     * XPath locator for "Default Company Registration Email" options.
     *
     * @var string
     */
    protected $options = '//*[@id="company_email_company_notify_admin_template"]/option';

    /**
     * XPath locator for select template.
     *
     * @var string
     */
    protected $selectTemplate = '//*[@id="company_email_company_notify_admin_template"]';

    /**
     * XPath locator for select template.
     *
     * @var string
     */
    protected $inheritTemplate = '//*[@id="company_email_company_notify_admin_template_inherit"]';

    /**
     * Css locator for company email options header.
     *
     * @var string
     */
    protected $emailOptions = '#company_email-head';

    /**
     * Check isset template.
     *
     * @param string $code
     * @return bool
     */
    public function issetTemplate($code)
    {
        if (!$this->_rootElement->find($this->selectTemplate, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->emailOptions)->click();
        }
        if ($this->_rootElement->find($this->inheritTemplate, Locator::SELECTOR_XPATH)->isSelected()) {
            $this->_rootElement->find($this->inheritTemplate, Locator::SELECTOR_XPATH)->click();
        }
        $this->_rootElement->find($this->selectTemplate, Locator::SELECTOR_XPATH)->click();
        $optionElements = $this->_rootElement->getElements($this->options, Locator::SELECTOR_XPATH);
        foreach ($optionElements as $optionElement) {
            if ($optionElement->getText() == $code) {
                return true;
            }
        }
        return false;
    }
}
