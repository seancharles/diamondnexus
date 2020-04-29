<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml\Edit\Tab;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;
use Magento\Company\Test\Block\Adminhtml\CustomerGroup;

/**
 * Class CustomerView.
 */
class CustomerView extends Block
{
    /**
     * Customer type.
     *
     * @var string
     */
    private $customerType = '//*[@id="container"]/div/div/div[2]/div/div/div[1]/table/tbody/tr[1]/td';

    /**
     * Company name.
     *
     * @var string
     */
    private $companyName = '//*[@id="container"]/div/div/div[2]/div/div/div[1]/table/tbody/tr[2]/td';

    /**
     * Customer group.
     *
     * @var string
     */
    private $customerGroup = '[name="customer[group_id]"]';

    /**
     * XPath locator for customer link.
     *
     * @var string
     */
    private $customerTab = '//span[contains(text(), \'Account Information\')]/ancestor::div[@class="fieldset-wrapper"]';

    /**
     * XPath locator for customer tab.
     *
     * @var string
     */
    private $customerLink = '//*[@id="tab_customer"]';

    /**
     * Get customer type.
     *
     * @return string
     */
    public function getCustomerType()
    {
        return trim($this->_rootElement->find($this->customerType, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Get company name.
     *
     * @return string
     */
    public function getCompanyName()
    {
        return trim($this->_rootElement->find($this->companyName, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Get customer group field.
     *
     * @return ElementInterface
     */
    public function getCustomerGroup()
    {
        if (!$this->_rootElement->find($this->customerTab, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->_rootElement->find($this->customerLink, Locator::SELECTOR_XPATH)->click();
        }
        return $this->_rootElement->find($this->customerGroup, Locator::SELECTOR_CSS, CustomerGroup::class);
    }
}
