<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Company\Test\Page\Company as CompanyPage;

/**
 * Assert that company admin role is disabled.
 */
class AssertCompanyAdminRoleIsDisabled extends AbstractConstraint
{
    /**
     * Company page.
     *
     * @var CompanyPage
     */
    private $companyPage;

    /**
     * Process assert.
     *
     * @param CompanyPage $companyPage
     * @param string $companyAdminRole
     * @return void
     */
    public function processAssert(CompanyPage $companyPage, $companyAdminRole)
    {
        $this->companyPage = $companyPage;
        $this->companyPage->open();
        $this->companyPage->getTree()->selectCompanyAdmin();
        $this->companyPage->getTreeControl()->clickEditSelected();
        $this->assertCompanyAdminRole($companyAdminRole);
        $this->assertCompanyAdminRoleSelectIsDisabled();
    }

    /**
     * Assert company admin role.
     *
     * @param $expectedCompanyAdminRole
     * @return void
     */
    private function assertCompanyAdminRole($expectedCompanyAdminRole)
    {
        $companyAdminRole = $this->companyPage->getCustomerPopup()->getCompanyAdminRole();

        \PHPUnit\Framework\Assert::assertEquals(
            $expectedCompanyAdminRole,
            $companyAdminRole,
            'Company admin role is incorrect.'
        );
    }

    /**
     * Assert company admin role select is disabled.
     *
     * @return void
     */
    private function assertCompanyAdminRoleSelectIsDisabled()
    {
        $isRoleSelectDisabled = $this->companyPage->getCustomerPopup()->isUserRoleSelectDisabled();

        \PHPUnit\Framework\Assert::assertTrue(
            $isRoleSelectDisabled,
            'Ability to edit company admin role is not disabled.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Company admin role is correct. Ability to edit company admin role is disabled.';
    }
}
