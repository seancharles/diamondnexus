<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block\Adminhtml;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Mtf\Client\Locator;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Fixture\Customer;

/**
 * Form for creation of the company.
 */
class CompanyForm extends FormSections
{
    /**
     * Css locator for loader.
     *
     * @var string
     */
    protected $loader = '.loading-mask';

    /**
     * Name of default tab.
     *
     * @var string
     */
    protected $defaultSectionName = 'settings';

    /**
     * Css locator for company status field.
     *
     * @var string
     */
    private $status = '[name="general[status]"]';

    /**
     * Css locator for company admin email field.
     *
     * @var string
     */
    private $adminEmail = 'input[name="company_admin[email]"]';

    /**
     * Css locator for company admin firstanme field.
     *
     * @var string
     */
    private $firstname = 'input[name="company_admin[firstname]"]';

    /**
     * Css locator for company admin lastname field.
     *
     * @var string
     */
    private $lastname = 'input[name="company_admin[lastname]"]';

    /**
     * Xpath locator for Proceed button.
     *
     * @var string
     */
    private $adminChangeProceedButton = '//button[@class="action-primary"]/span[contains(., "Proceed")]';

    /**
     * Css locator for Change Status button.
     *
     * @var string
     */
    private $changeStatusButton = '[data-role="modal"].change-status button.action-primary';

    /**
     * Xpath locator for reject reason field.
     *
     * @var string
     */
    private $rejectReasonField = '//div[@class="admin__control-block admin__reason-rejection"]/textarea';

    /**
     * Selector for option.
     *
     * @var string
     */
    private $customerGroup = './/*[text()[contains(., "%s")]]';

    /**
     * Locator for Customer Group element.
     *
     * @var string
     */
    private $customerGroupField = '[data-action="open-search"]';

    /**
     * Change company admin.
     *
     * @param Company|Customer $source
     * @return void
     */
    public function changeCompanyAdmin($source)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->openSection('company_admin');
        $this->waitForElementVisible($this->adminEmail);
        $this->_rootElement->find($this->adminEmail)->setValue($source->getEmail());
        $this->waitForElementVisible($this->adminChangeProceedButton, Locator::SELECTOR_XPATH);
        $this->waitForElementNotVisible($this->loader);
        // wait for random second loader to disappear
        sleep(1);
        $this->browser->find($this->adminChangeProceedButton, Locator::SELECTOR_XPATH)->click();
        $this->setAdminData($source);
    }

    /**
     * Returns company reject reason.
     *
     * @return string
     */
    public function getReasonForReject()
    {
        return $this->_rootElement->find($this->rejectReasonField, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Fill company admin firstname and lastname.
     *
     * @param Company|Customer $source
     * @return void
     */
    public function setAdminData($source)
    {
        $this->openSection('company_admin');
        $this->_rootElement
            ->find($this->firstname)
            ->setValue($source->getFirstname());
        $this->_rootElement
            ->find($this->lastname)
            ->setValue($source->getLastname());
    }

    /**
     * Get company admin email.
     *
     * @return string
     */
    public function getAdminEmail()
    {
        $this->openSection('company_admin');
        $this->waitForElementVisible($this->adminEmail);
        return $this->_rootElement->find($this->adminEmail)->getValue();
    }

    /**
     * Set company admin email.
     *
     * @param string $adminEmail
     * @return void
     */
    public function setAdminEmail($adminEmail)
    {
        $sectionId = 'company_admin';
        $this->waitForElementVisible($this->containers[$sectionId]['selector']);
        $this->openSection($sectionId);
        $this->waitForElementVisible($this->adminEmail);
        $this->_rootElement->find($this->adminEmail)->setValue($adminEmail);
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Fill customer group.
     *
     * @param string $groupName
     * @return void
     */
    public function fillCustomerGroup($groupName)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->openSection('settings');

        $this->_rootElement->find($this->customerGroupField, Locator::SELECTOR_CSS)->click();
        $this->_rootElement->find(sprintf($this->customerGroup, $groupName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Change company status.
     *
     * @param string $status
     * @return void
     */
    public function setCompanyStatus($status)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->status, Locator::SELECTOR_CSS, 'select')->setValue($status);
        $this->waitForElementVisible($this->changeStatusButton);
        $this->browser->find($this->changeStatusButton)->click();
    }
}
