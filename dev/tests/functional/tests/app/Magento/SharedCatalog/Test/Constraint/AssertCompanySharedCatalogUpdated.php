<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert shared catalog updated on company
 */
class AssertCompanySharedCatalogUpdated extends AbstractConstraint
{

    public function processAssert(
        Company $company,
        SharedCatalog $catalog,
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $filter = ['company_name' => $company->getCompanyName()];

        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen($filter);

        $data = $companyEdit->getCompanyForm()->getData();

        \PHPUnit\Framework\Assert::assertEquals(
            $data['customer_group_id'],
            $catalog->getName(),
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
        return 'Shared catalog was updated on company.';
    }
}
