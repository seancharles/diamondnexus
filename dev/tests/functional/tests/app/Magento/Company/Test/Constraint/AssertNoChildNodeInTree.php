<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractAssertForm;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert that there are no children in tree
 */
class AssertNoChildNodeInTree extends AbstractAssertForm
{
    /**
     * Assert that there are no children in tree
     *
     * @param CompanyPage $companyPage
     */
    public function processAssert(
        CompanyPage $companyPage
    ) {
        $companyPage->open();
        \PHPUnit\Framework\Assert::assertFalse($companyPage->getTree()->hasChildren());
    }

    /**
     * There is no children in tree
     *
     * @return string
     */
    public function toString()
    {
        return 'There is no children in tree.';
    }
}
