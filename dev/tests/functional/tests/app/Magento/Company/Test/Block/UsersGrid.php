<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class UsersGrid.
 */
class UsersGrid extends Block
{
    /**
     * Css locator for loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Css locator for loader in users grid.
     *
     * @var string
     */
    private $usersGridLoader = '.admin__data-grid-loading-mask';

    /**
     * Xpath locator edit user link.
     *
     * @var string
     */
    private $editUserLink = '//table/tbody/tr/td/div[contains(text(), "%s")]/../../td/a[contains(text(), "Edit")]';

    /**
     * Xpath locator edit user link.
     *
     * @var string
     */
    private $deleteUserLink = '//table/tbody/tr/td/div[contains(text(), "%s")]/../../td/a[contains(text(), "Delete")]';

    /**
     * Xpath locator show inactive users link.
     *
     * @var string
     */
    private $showInactiveUsers =
        '//div[@class="data-grid-filters-wrap _show"]/button/span[contains(text(), "Show Inactive Users")]/..';

    /**
     * Xpath locator show all users link.
     *
     * @var string
     */
    private $showAllUsers = '//div[@class="data-grid-filters-wrap _show"]/button[contains(text(), "Show All Users")]';

    /**
     * Xpath locator show inactive users link.
     *
     * @var string
     */
    private $showActiveUsers =
        '//div[@class="data-grid-filters-wrap _show"]/button/span[contains(text(), "Show Active Users")]/..';

    /**
     * Xpath locator user's email cell in the grid.
     *
     * @var string
     */
    private $userEmail = '//tr/td/div[contains(text(), "%s")]';

    /**
     * Click edit user link.
     *
     * @param string $email
     * @return void
     */
    public function clickEditUser($email)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find(sprintf($this->editUserLink, $email), Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click delete user link.
     *
     * @param string $email
     * @return void
     */
    public function clickDeleteUser($email)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find(sprintf($this->deleteUserLink, $email), Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click show all users.
     *
     * @return void
     */
    public function clickShowAllUsers()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->usersGridLoader);
        $this->_rootElement->find($this->showAllUsers, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click show inactive users.
     *
     * @return void
     */
    public function clickShowInactiveUsers()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->usersGridLoader);
        $this->_rootElement->find($this->showInactiveUsers, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Click show active users.
     *
     * @return void
     */
    public function clickShowActiveUsers()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->usersGridLoader);
        $this->_rootElement->find($this->showActiveUsers, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Check if user presents in the grid.
     *
     * @param string $email
     * @return bool
     */
    public function isUserInGrid($email)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->waitForElementNotVisible($this->usersGridLoader);
        return $this->_rootElement->find(sprintf($this->userEmail, $email), Locator::SELECTOR_XPATH)->isVisible();
    }
}
