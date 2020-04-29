<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Page\Adminhtml\SystemConfigBtob;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that system configuration B2B Shared Catalog feature comment is correct.
 */
class AssertSystemConfigBtobSectionSharedCatalogItemComment extends AbstractConstraint
{
    /**
     * Check that system configuration B2B Shared Catalog feature comment is correct.
     *
     * @param SystemConfigBtob $systemConfigBtob
     * @param string $sharedCatalogItemComment
     * @return void
     */
    public function processAssert(SystemConfigBtob $systemConfigBtob, $sharedCatalogItemComment)
    {
        \PHPUnit\Framework\Assert::assertEquals(
            $sharedCatalogItemComment,
            $systemConfigBtob->getBtobFeatures()->getSharedCatalogItemComment(),
            'System configuration B2B section shared catalog item comment is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'System configuration B2B section shared catalog item comment is correct.';
    }
}
