<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Block\Html;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\ElementInterface;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Class Topmenu.
 * Class top menu navigation block.
 */
class NavigationMenu extends Block
{
    /**
     * Link with category name.
     *
     * @var string
     */
    private $category = './/a[span="%s"]';

    /**
     * Top Elements of menu.
     *
     * @var string
     */
    private $navigationMenuItems = "li.level0";

    /**
     * Submenu element.
     *
     * @var string
     */
    private $submenu = "ul.submenu";

    /**
     * Submenu items.
     *
     * @var string
     */
    private $submenuItems = "ul.submenu > li.level";

    /**
     * Last top-level category in the menu.
     *
     * @var string
     */
    private $lastCategory = 'li.level0:last-child > a';

    /**
     * Logo element.
     *
     * @var string
     */
    private $logo = '.logo';

    /**
     * Select category from top menu by name and click on it.
     *
     * @param string $categoryName
     * @return void
     */
    public function selectCategoryByName($categoryName)
    {
        $category = $this->_rootElement->find(sprintf($this->category, $categoryName), Locator::SELECTOR_XPATH);
        if ($category->isVisible()) {
            $category->click();
            return;
        }

        $items = $this->_rootElement->getElements($this->navigationMenuItems);
        $this->walkSubmenu($items, $category, 1, true);
    }

    /**
     * Check is visible category in top menu by name.
     *
     * @param string $categoryName
     * @return bool
     */
    public function isCategoryVisible($categoryName)
    {
        $category = $this->_rootElement->find(sprintf($this->category, $categoryName), Locator::SELECTOR_XPATH);
        if ($category->isVisible()) {
            return true;
        }

        $items = $this->_rootElement->getElements($this->navigationMenuItems);
        return $this->walkSubmenu($items, $category, 1);
    }

    /**
     * Check all submenu.
     *
     * @param \Magento\Mtf\Client\ElementInterface[] $items
     * @param \Magento\Mtf\Client\ElementInterface $category
     * @param int $level
     * @param bool $click
     * @return bool
     */
    private function walkSubmenu(array $items, ElementInterface $category, $level, $click = false)
    {
        $result = false;
        foreach ($items as $item) {
            $subitems = $item->getElements($this->submenuItems . $level);
            if (!empty($subitems)) {
                $item->waitUntil(
                    function () use ($item, $subitems) {
                        $item->hover();
                        return $subitems[0]->isVisible() ? true : null;
                    }
                );
                if ($category->isVisible()) {
                    if ($click) {
                        $category->click();
                    }
                    return true;
                }
                $result = $result || $this->walkSubmenu($subitems, $category, $level + 1, $click);
                if ($result) {
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Check by name if category is present in top menu.
     *
     * @param string $categoryName
     * @return bool
     */
    public function isCategoryPresentInMenu($categoryName)
    {
        $result = false;
        $category = $this->_rootElement->getElements(sprintf($this->category, $categoryName), Locator::SELECTOR_XPATH);
        if (!empty($category)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Get nesting level of category in the top menu.
     *
     * @param Category $category
     * @return int|null
     */
    public function getCategoryNestingLevel(Category $category)
    {
        $this->browser->find($this->logo)->hover();
        $this->expandMenu($category);
        $element = $this->_rootElement->find(
            sprintf($this->category . '/..', $category->getName()),
            Locator::SELECTOR_XPATH
        );
        preg_match('/level([^\s]*)/', $element->getAttribute('class'), $matches);
        if (!empty($matches[1])) {
            return (int)$matches[1];
        }
        return null;
    }

    /**
     * Expand menu to reach nested category.
     *
     * @param Category $category
     * @return void
     */
    private function expandMenu(Category $category)
    {
        $element = $this->_rootElement->find(
            sprintf($this->category . '/..', $category->getName()),
            Locator::SELECTOR_XPATH
        );
        if (!$element->isPresent() || !$element->isVisible()) {
            $parent = $category->getDataFieldConfig('parent_id')['source']->getParentCategory();
            if ($parent) {
                $this->expandMenu($parent);
            }
        }
        if ($element->isPresent() && $element->find($this->submenu)->isPresent()) {
            $element->hover();
            $submenu = $element->find($this->submenu);
            $element->waitUntil(
                function () use ($submenu) {
                    return $submenu->isVisible() ? true : null;
                }
            );
        }
    }

    /**
     * Get name of the last top-level category in the menu.
     *
     * @return string
     */
    public function getLastCategoryName()
    {
        return $this->_rootElement->find($this->lastCategory)->getText();
    }

    /**
     * Check if there are no categories in the top menu.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_rootElement->getElements($this->navigationMenuItems));
    }
}
