<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Element;

use Magento\Mtf\Client\Locator;

/**
 * Element of Role Permissions tree.
 */
class RolePermissionTreeElement extends \Magento\Mtf\Client\Element\JquerytreeElement
{
    /**
     * Pattern for child element node.
     *
     * @var string
     */
    protected $pattern = '/ul/li[contains(@class, "jstree-node")]/a[text() = "%s"]/..';

    /**
     * Pattern for child open node.
     *
     * @var string
     */
    protected $openNode = '//li[contains(@class, "jstree-open") or contains(@class, "jstree-leaf")]'
                            . '/a[text() = "%s"]/..';

    /**
     * Pattern for child closed node.
     *
     * @var string
     */
    protected $closedNode = '//li[contains(@class, "jstree-closed") or contains(@class, "jstree-leaf")]'
                            . '/a[text() = "%s"]/..';

    /**
     * Selected checkboxes.
     *
     * @var string
     */
    protected $selectedLabels = '//li/a[contains(@class, "jstree-clicked")]';

    /**
     * Selected checkboxes by level.
     *
     * @var string
     */
    protected $selectedLabelsByLevel = '/ul/li/a[contains(@class, "jstree-clicked")]';

    /**
     * Selector for expansion control.
     *
     * @var string
     */
    protected $expansionControl = '/i[contains(@class, "jstree-ocl")]';

    /**
     * Selector for input.
     *
     * @var string
     */
    protected $input = '/a/i[contains(@class, "jstree-checkbox")]';

    /**
     * Display children.
     *
     * @param string $elementLabel
     * @return void
     */
    protected function displayChildren($elementLabel)
    {
        $element = $this->find(sprintf($this->openNode, $elementLabel), Locator::SELECTOR_XPATH);
        if ($element->isVisible()) {
            return;
        }
        $plusButton = $this->find(
            sprintf($this->closedNode, $elementLabel) . $this->expansionControl,
            Locator::SELECTOR_XPATH
        );
        if ($plusButton->isVisible()) {
            $plusButton->click();
        }
    }
}
