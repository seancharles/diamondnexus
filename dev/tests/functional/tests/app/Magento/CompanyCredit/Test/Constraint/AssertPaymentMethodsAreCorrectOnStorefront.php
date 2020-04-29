<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

/**
 * Assert that Payment Methods are correct on Company Profile page on Storefront.
 */
class AssertPaymentMethodsAreCorrectOnStorefront extends \Magento\Mtf\Constraint\AbstractConstraint
{
    /**
     * Process assert.
     *
     * @param \Magento\Company\Test\Page\CompanyProfile $companyProfile
     * @param string|null $expectedMethods
     * @return void
     */
    public function processAssert(
        \Magento\Company\Test\Page\CompanyProfile $companyProfile,
        $expectedMethods = null
    ) {
        $companyProfile->open();
        $this->checkPaymentMethods($expectedMethods, $companyProfile);
    }

    /**
     * Check payment methods.
     *
     * @param string $expectedMethods
     * @param \Magento\Company\Test\Page\CompanyProfile $companyProfile
     * @return void
     */
    public function checkPaymentMethods($expectedMethods, \Magento\Company\Test\Page\CompanyProfile $companyProfile)
    {
        $isValid = true;
        $expectedMethods = explode(',', $expectedMethods);
        $availablePaymentMethods = $companyProfile->getCompanyProfilePaymentMethods()->getAvailablePaymentMethods();

        foreach ($expectedMethods as $expectedMethod) {
            if (strpos($availablePaymentMethods, $expectedMethod) === false) {
                $isValid = false;
            }
        }

        \PHPUnit\Framework\Assert::assertTrue(
            $isValid,
            'Payment method list in company profile page is incorrect.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return 'Matched payment method list is correct.';
    }
}
