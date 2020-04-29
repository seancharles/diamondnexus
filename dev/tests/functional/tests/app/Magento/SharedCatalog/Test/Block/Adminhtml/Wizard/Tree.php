<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Adminhtml\Wizard;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;

/**
 * Tree of categories.
 */
class Tree extends Block
{
    /** @var string */
    protected $expandClass = 'button[data-button-state="expand"]';

    /** @var string */
    protected $loader = [
        'structure' => '#catalog-steps-wizard_step_structure div.configure-step-left [data-role="spinner"]',
        'pricing' => '#catalog-steps-wizard_step_pricing div.configure-step-left [data-role="spinner"]',
        'state' => '.shared-catalog-config-container .jstree-spinner-helper',
    ];

    /** @var Block */
    protected $locateNodeByName = '//div[@data-role="jstree-shared-catalog-container"]//a[contains(., "%s")]';

    /** @var string */
    protected $type = 'structure';

    /**
     * @var string
     */
    private $rootCategoryName = 'Root Catalog';

    /**
     * Included products counter css selector.
     *
     * @var string
     */
    private $includedProductsCounter = 'a[data-category-name="%s"] div.jstree-item-quantity';

    /**
     * Set tree type.
     *
     * @param string $type
     * @return $this
     */
    public function setTreeType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Expand tree elements.
     *
     * @return Tree
     */
    public function expandAll()
    {
        $this->waitForLoad();
        $this->_rootElement->find($this->expandClass)->click();
        return $this;
    }

    /**
     * Find tree node.
     *
     * @param string $nodeName
     * @return ElementInterface
     */
    public function findTreeNode($nodeName)
    {
        $locator = 'a[data-category-name="%s"]';
        $this->waitForLoad();
        return $this->_rootElement->find(sprintf($locator, $nodeName));
    }

    /**
     * Select node by $nodeName in the tree.
     *
     * @param string $nodeName
     * @return void
     */
    public function selectNode($nodeName)
    {
        $this->findTreeNode($nodeName)->click();
        $this->waitForLoad();
    }

    /**
     * Select root category node in the tree.
     *
     * @return void
     */
    public function selectRootNode()
    {
        $this->selectNode($this->rootCategoryName);
    }

    /**
     * Find tree node checkbox.
     *
     * @param string $nodeName
     * @return ElementInterface
     */
    public function findTreeNodeCheckbox($nodeName)
    {
        $locator = 'a[data-category-name="%s"] i';
        $this->waitForLoad();
        return $this->_rootElement->find(sprintf($locator, $nodeName));
    }

    /**
     * Check/uncheck node in the tree.
     *
     * @param string $nodeName
     * @return void
     */
    public function toggleNode($nodeName)
    {
        $this->findTreeNodeCheckbox($nodeName)->click();
        $this->waitForElementVisible($this->loader[$this->type]);
        $this->waitForLoad();
    }

    /**
     * Wait for load.
     *
     * @return Tree
     */
    public function waitForLoad()
    {
        $this->waitForElementNotVisible($this->loader[$this->type]);
        return $this;
    }

    /**
     * Get node products count.
     *
     * @param string $nodeName
     * @return string
     */
    public function getProductCount($nodeName)
    {
        $this->waitForLoad();
        return $this->_rootElement->find(sprintf($this->includedProductsCounter, $nodeName))->getText();
    }
}
