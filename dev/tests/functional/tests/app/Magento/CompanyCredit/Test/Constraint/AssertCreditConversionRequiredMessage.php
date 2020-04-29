<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEditSectionCurrency;
use Magento\Company\Test\Fixture\Company;

/**
 * Assert that correct credit update required warning message is displayed.
 */
class AssertCreditConversionRequiredMessage extends AbstractConstraint
{
    // @codingStandardsIgnoreStart
    /**
     * Expected credit update required warning message.
     */
    private $expectedMessage = 'The base currency for %s has been updated. The currency %s is still defined as the credit currency for one or more companies. Use the following link to perform a bulk operation that updates the credit currency for those companies.';
    // @codingStandardsIgnoreEnd

    /**
     * Assert that correct credit update required warning message is displayed.
     *
     * @param SystemConfigEditSectionCurrency $systemConfig
     * @param Company $company
     * @param string $websiteName
     * @return void
     */
    public function processAssert(
        SystemConfigEditSectionCurrency $systemConfig,
        Company $company,
        $websiteName
    ) {
        $message = sprintf(
            $this->expectedMessage,
            $websiteName,
            $company->getCurrencyCode()
        );

        \PHPUnit\Framework\Assert::assertEquals(
            $message,
            $systemConfig->getMessagesBlock()->getWarningMessage(),
            'Credit update required warning message is incorrect.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Credit update required warning message is correct.';
    }
}
