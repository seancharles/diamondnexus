<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Page\CompanyProfile as CompanyProfilePage;

/**
 * Assert that edit button is not visible when logged in as company user
 */
class AssertEditButtonNotVisible extends AbstractConstraint
{
    /**
     * Assert that edit button is not visible when logged in as company user
     *
     * @param CompanyProfilePage $companyProfilePage
     * @param Customer $companyUser
     * @param ObjectManager $objectManager
     */
    public function processAssert(
        CompanyProfilePage $companyProfilePage,
        Customer $companyUser,
        ObjectManager $objectManager
    ) {
        $objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $companyUser]
        )->run();
        $companyProfilePage->open();

        \PHPUnit\Framework\Assert::assertFalse(
            $companyProfilePage->getProfileContent()->isEditButtonVisible(),
            'Edit button is visible.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Edit button is not visible.';
    }
}
