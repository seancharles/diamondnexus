<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class RolesGrid.
 */
class RolesGrid extends Block
{
    /**
     * Css selector add new role button.
     *
     * @var string
     */
    private $addNewRoleButton = '.column.main .actions button';

    /**
     * Xpath locator edit role.
     *
     * @var string
     */
    private $editRoleLink = '//td[@data-th="Role" and div[contains(text(), "%s")]]/..'
                            . '/td[@data-th="Actions"]/a[contains(text(), "Edit")]';

    /**
     * Css selector loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Click add new role button.
     *
     * @return void;
     */
    public function addNewRole()
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find($this->addNewRoleButton)->click();
    }

    /**
     * Click edit role link.
     *
     * @param string $roleName
     * @return void
     */
    public function editRole($roleName)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->_rootElement->find(sprintf($this->editRoleLink, $roleName), Locator::SELECTOR_XPATH)->click();
    }
}
