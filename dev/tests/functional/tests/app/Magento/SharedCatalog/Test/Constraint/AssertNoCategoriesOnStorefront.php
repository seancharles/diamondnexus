<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that top menu is empty on the Storefront.
 */
class AssertNoCategoriesOnStorefront extends AbstractConstraint
{
    /**
     * Assert that there are no categories in the top menu.
     *
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function processAssert(CmsIndex $cmsIndex)
    {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getNavigationMenu()->isEmpty(),
            'Navigation menu is not empty.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'There are no categories in the top menu on the Storefront.';
    }
}
