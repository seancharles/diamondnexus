<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Client\Locator;

/**
 * User popup block.
 */
class CustomerPopup extends AbstractPopup
{

    /**
     * Css selector fot email error message.
     *
     * @var string
     */
    protected $errorMessage = '#email-error';

    /**
     * CSS selector for Magento loader.
     *
     * @var string
     */
    private $loader = '[data-role="loader"]';

    /**
     * Css selector for job title.
     *
     * @var string
     */
    protected $jobTitle = '[name="extension_attributes[company_attributes][job_title]"]';

    /**
     * Css selector for telephone.
     *
     * @var string
     */
    protected $telephone = '[name="extension_attributes[company_attributes][telephone]"]';

    /**
     * Css selector role select.
     *
     * @var string
     */
    protected $userRoleSelect = '[name=role]';

    /**
     * Xpath locator user role select option.
     *
     * @var string
     */
    protected $userRoleSelectOption = '//select[@name="role"]/option[contains(text(), "%s")]';

    /**
     * Xpath locator user role select option selected.
     *
     * @var string
     */
    protected $userRoleSelectOptionSelected = '//select[@name="role"]/option[@selected="selected"]';

    /**
     * Xpath locator company admin role.
     *
     * @var string
     */
    protected $companyAdminRole = '//select[@name="role"]/option[@value="0"]';

    /**
     * Css selector user status select.
     *
     * @var string
     */
    protected $userStatusSelect = '[name="extension_attributes[company_attributes][status]"]';

    /**
     * User status Active text value.
     *
     * @var string
     */
    protected $statusValueActive = 'Active';

    /**
     * Returns email error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_rootElement->find($this->errorMessage)->getText();
    }

    /**
     * Fill the root form.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $mapping = $this->dataMapping($fixture->getData());

        // Fill email and wait for loader
        $this->_fill(['email' => $mapping['email']]);
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->click();
        $this->waitForElementNotVisible($this->loader);

        // Fill the rest
        unset($mapping['email']);
        $this->_fill($mapping, $element);

        return $this;
    }

    /**
     * Set job title.
     *
     * @param string $jobTitle
     * @return void
     */
    public function setJobTitle($jobTitle)
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->jobTitle)->setValue($jobTitle);
    }

    /**
     * Set telephone.
     *
     * @param string $telephone
     * @return void
     */
    public function setTelephone($telephone)
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->telephone)->setValue($telephone);
    }

    /**
     * Wait popup to load.
     *
     * @return void
     */
    public function waitPopupToLoad()
    {
        $this->waitForElementNotVisible($this->loadingMask);
    }

    /**
     * Select user role.
     *
     * @param string $roleName
     * @return void
     */
    public function selectUserRole($roleName)
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->userRoleSelect)->click();
        $this->_rootElement->find(sprintf($this->userRoleSelectOption, $roleName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Is user role select disabled.
     *
     * @return bool
     */
    public function isUserRoleSelectDisabled()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        return $this->_rootElement->find($this->userRoleSelect)->isDisabled();
    }

    /**
     * Get user role.
     *
     * @return string
     */
    public function getUserRole()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        return trim($this->_rootElement->find($this->userRoleSelectOptionSelected, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Get company admin role.
     *
     * @return string
     */
    public function getCompanyAdminRole()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        return trim($this->_rootElement->find($this->companyAdminRole, Locator::SELECTOR_XPATH)->getText());
    }

    /**
     * Set user status active.
     *
     * @return void
     */
    public function setUserStatusActive()
    {
        $this->setUserStatus($this->statusValueActive);
    }

    /**
     * Add customer.
     *
     * @param FixtureInterface $customer
     * @return void
     */
    public function addCustomer(FixtureInterface $customer)
    {
        $this->fill($customer);
        $this->setJobTitle($customer->getJobTitle());
        $this->setTelephone($customer->getTelephone());
        $this->submit();
    }

    /**
     * Change role.
     *
     * @param $roleName
     * @return void
     */
    public function changeRole($roleName)
    {
        $this->selectUserRole($roleName);
        $this->submit();
    }

    /**
     * Set user status.
     *
     * @param string $status
     * @return void
     */
    private function setUserStatus($status)
    {
        $this->_rootElement->find($this->userStatusSelect, Locator::SELECTOR_CSS, 'select')->setValue($status);
    }
}
