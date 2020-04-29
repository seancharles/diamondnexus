<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\Constraint;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Company\Test\Page\CompanyAccessDenied;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;

/**
 * Assert that quote view is restricted.
 */
class AssertQuoteViewIsRestricted extends AbstractConstraint
{
    /**
     * @param NegotiableQuoteGrid $negotiableQuoteGrid
     * @param CompanyAccessDenied $accessDeniedPage
     * @param CmsPage $deniedPage
     */
    public function processAssert(
        NegotiableQuoteGrid $negotiableQuoteGrid,
        CompanyAccessDenied $accessDeniedPage,
        CmsPage $deniedPage
    ) {
        $negotiableQuoteGrid->open();
        $negotiableQuoteGrid->getQuoteGrid()->openFirstItem();
        $heading = $accessDeniedPage->getAccessDenied()->getContentHeading();
        $content = $accessDeniedPage->getAccessDenied()->getPageContent();
        \PHPUnit\Framework\Assert::assertTrue(
            $this->isPageValid($deniedPage, $heading, $content),
            'Quote view is not restricted.'
        );
    }

    /**
     * Checks is page content valid.
     *
     * @param CmsPage $deniedPage
     * @param string $heading
     * @param string $content
     * @return bool
     */
    private function isPageValid(CmsPage $deniedPage, $heading, $content)
    {
        return $deniedPage->getContentHeading() == $heading && $deniedPage->getContent()['content'] == $content;
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Quote view is restricted.';
    }
}
