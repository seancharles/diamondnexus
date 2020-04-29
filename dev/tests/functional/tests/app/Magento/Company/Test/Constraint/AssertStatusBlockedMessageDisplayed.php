<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert that status blocked message is displayed
 */
class AssertStatusBlockedMessageDisplayed extends AbstractConstraint
{
    /**
     * Assert that status blocked message is displayed
     *
     * @param BrowserInterface $browser
     */
    public function processAssert(BrowserInterface $browser)
    {
        $message = 'Your company account is blocked and you cannot place orders. '
            . 'If you have questions, please contact your company administrator.';

        \PHPUnit\Framework\Assert::assertTrue(
            $browser->find('.message.company-warning')->isVisible(),
            'Status blocked message is not visible.'
        );
        if ($browser->find('.message.company-warning')->isVisible()) {
            \PHPUnit\Framework\Assert::assertEquals(
                $message,
                $browser->find('.message.company-warning')->getText(),
                'Status blocked message is not visible or incorrect.'
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Status blocked message is visible.';
    }
}
