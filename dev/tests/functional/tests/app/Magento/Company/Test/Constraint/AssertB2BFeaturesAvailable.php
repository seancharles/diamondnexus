<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Cms\Test\Page\CmsIndex;

/**
 * Assert that all B2B features are available
 */
class AssertB2BFeaturesAvailable extends AbstractConstraint
{

    /**
     * Assert that all B2B features are available
     *
     * @param CmsIndex $cmsIndex
     */
    public function processAssert(CmsIndex $cmsIndex)
    {
        $cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->verifyMyQuotesLink($cmsIndex);
        $this->verifyRequisitionListLink($cmsIndex);
        $this->verifyCompanyLink($cmsIndex);
        $this->verifyQuickOrderLink($cmsIndex);
    }

    /**
     * Verify that "My Quotes" link is visible in customer menu
     *
     * @param CmsIndex $cmsIndex
     */
    protected function verifyMyQuotesLink(CmsIndex $cmsIndex)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('My Quotes'),
            '"My Quotes" link is not visible.'
        );
    }

    /**
     * Verify that "Requisition Lists" link is visible in customer menu
     *
     * @param CmsIndex $cmsIndex
     */
    protected function verifyRequisitionListLink(CmsIndex $cmsIndex)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('Requisition Lists'),
            '"Requisition Lists" link is not visible.'
        );
    }

    /**
     * Verify that "My Company" link is visible in customer menu
     *
     * @param CmsIndex $cmsIndex
     */
    protected function verifyCompanyLink(CmsIndex $cmsIndex)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('Company Structure'),
            '"My Company" link is not visible.'
        );
    }

    /**
     * Verify that "Quick Order" link is visible in customer menu
     *
     * @param CmsIndex $cmsIndex
     */
    protected function verifyQuickOrderLink(CmsIndex $cmsIndex)
    {
        \PHPUnit\Framework\Assert::assertTrue(
            $cmsIndex->getLinksBlock()->isLinkVisible('Quick Order'),
            '"Quick Order" link is not visible.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'All B2B features are available.';
    }
}
