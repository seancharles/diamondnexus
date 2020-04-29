<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert shared catalog deleted on company.
 */
class AssertCompanySharedCatalogDeleted extends AbstractConstraint
{
    /**
     * Assert shared catalog deleted on company.
     *
     * @param Company $company
     * @param string $publicName
     * @param CompanyEdit $companyEdit
     * @return void
     * @throws \Exception
     */
    public function processAssert(
        Company $company,
        $publicName,
        CompanyEdit $companyEdit
    ) {
        $companyEdit->open(['id' => $company->getId()]);
        $data = $companyEdit->getCompanyForm()->getData();

        \PHPUnit\Framework\Assert::assertEquals(
            $data['customer_group_id'],
            $publicName,
            'Shared catalog is wrong on a company page.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog was deleted on company.';
    }
}
