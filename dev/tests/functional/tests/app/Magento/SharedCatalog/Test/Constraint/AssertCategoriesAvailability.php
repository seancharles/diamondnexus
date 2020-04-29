<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that categories are/aren't available in the top menu.
 */
class AssertCategoriesAvailability extends AbstractConstraint
{
    /**
     * Assert that nesting levels of categories are correct in the top menu.
     *
     * @param CmsIndex $cmsIndex
     * @param array $categoriesAvailability
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        array $categoriesAvailability
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        foreach ($categoriesAvailability as $data) {
            \PHPUnit\Framework\Assert::assertEquals(
                $data['available'],
                $cmsIndex->getNavigationMenu()->isCategoryPresentInMenu($data['category']->getName()),
                sprintf(
                    'Category \'%s\' is %s in the top menu.',
                    $data['category']->getName(),
                    $data['available'] ? 'absent' : 'present'
                )
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
        return 'Categories availability in the top menu is correct.';
    }
}
