<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that nesting levels of categories are correct in the top menu.
 */
class AssertCategoryLevelInMenu extends AbstractConstraint
{
    /**
     * Assert that nesting levels of categories are correct in the top menu.
     *
     * @param CmsIndex $cmsIndex
     * @param array $categoryLevels
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        array $categoryLevels
    ) {
        foreach ($categoryLevels as $data) {
            $cmsIndex->open();
            $cmsIndex->getLinksBlock()->waitWelcomeMessage();
            \PHPUnit\Framework\Assert::assertEquals(
                $data['level'],
                $cmsIndex->getNavigationMenu()->getCategoryNestingLevel($data['category']),
                sprintf('Category \'%s\' is displayed at a wrong nesting level.', $data['category']->getName())
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Nesting levels of categories are correct in the top menu.';
    }
}
