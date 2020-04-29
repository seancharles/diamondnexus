<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Tree controls block.
 */
class Tree extends Block
{
    /**
     * Selector of the children.
     *
     * @var string
     */
    protected $children = 'li.jstree-leaf a:not(.company-admin)';

    /**
     * Css selector company admin clicked.
     *
     * @var string
     */
    protected $companyAdminSelected = 'li.jstree-node a.company-admin.jstree-clicked';

    /**
     * Selected selector.
     *
     * @var string
     */
    protected $companyUserSelected = 'li.jstree-leaf a.jstree-clicked';

    /**
     * Loading mask selector.
     *
     * @var string
     */
    protected $loadingMask = '.loading-mask';

    /**
     * Css selector company admin.
     *
     * @var string
     */
    protected $companyAdmin = 'li.jstree-node.jstree-open a.company-admin';

    /**
     * Xpath locator company user.
     *
     * @var string
     */
    private $companyUser = '//div[@id="company-tree"]/ul/li/ul/li/a[contains(text(), "%s")]';

    /**
     * Selects first child.
     *
     * @return void
     */
    public function selectFirstChild()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->children)->click();
        $this->waitForElementVisible($this->companyUserSelected);
    }

    /**
     * Checks if tree root element has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        return (bool)count($this->_rootElement->getElements($this->children));
    }

    /**
     * Click company admin node.
     *
     * @return void
     */
    public function selectCompanyAdmin()
    {
        $this->waitForElementNotVisible($this->loadingMask);
        $this->_rootElement->find($this->companyAdmin)->click();
        $this->waitForElementVisible($this->companyAdminSelected);
    }

    /**
     * Assign child user to the user.
     *
     * @param string $parentUserEmail
     * @param string $childUserEmail
     * @return void
     */
    public function assignChildUser($parentUserEmail, $childUserEmail)
    {
        $this->_rootElement->find(sprintf($this->companyUser, $childUserEmail), Locator::SELECTOR_XPATH)->click();
        $targetElement = $this->_rootElement->find(
            sprintf($this->companyUser, $parentUserEmail),
            Locator::SELECTOR_XPATH
        );
        $this->_rootElement->find(sprintf($this->companyUser, $childUserEmail), Locator::SELECTOR_XPATH)
            ->dragAndDrop($targetElement);
    }

    /**
     * Is user in company tree.
     *
     * @param string $userName
     * @return bool
     */
    public function isUserInCompanyTree($userName)
    {
        $this->waitForElementNotVisible($this->loadingMask);
        return $this->_rootElement->find(sprintf($this->companyUser, $userName), Locator::SELECTOR_XPATH)->isVisible();
    }
}
