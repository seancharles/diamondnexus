<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that last top-level category in the top menu is correct.
 */
class AssertLastCategoryInMenu extends AbstractConstraint
{
    /**
     * Assert last top-level category in the top menu is correct.
     *
     * @param CmsIndex $cmsIndex
     * @param string $lastCategoryName
     * @return void
     */
    public function processAssert(
        CmsIndex $cmsIndex,
        $lastCategoryName
    ) {
        $cmsIndex->open();
        $cmsIndex->getLinksBlock()->waitWelcomeMessage();
        \PHPUnit\Framework\Assert::assertEquals(
            $lastCategoryName,
            $cmsIndex->getNavigationMenu()->getLastCategoryName(),
            'The last top-level category in the top menu is wrong.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'The last top-level category in the top menu is correct.';
    }
}
