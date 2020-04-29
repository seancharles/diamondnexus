<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Company\Test\Fixture\Company;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create shared catalog.
 * 2. Create company
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Go to Customers > Companies.
 * 3. Open created company.
 * 4. Change shared catalog.
 * 5. Save company.
 * 6. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-67972
 */
class UpdateCompanySharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /** @var CompanyIndex */
    protected $companyIndex;

    /** @var CompanyEdit */
    protected $companyEdit;

    /**
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @return void
     */
    public function __inject(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit
    ) {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
    }

    /**
     * Update Shared Catalog
     *
     * @param Company $initialCompany
     * @param SharedCatalog $sharedCatalog
     * @return array
     */
    public function test(Company $initialCompany, SharedCatalog $sharedCatalog)
    {
        $sharedCatalog->persist();
        $initialCompany->persist();

        $filter = ['company_name' => $initialCompany->getCompanyName()];

        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);

        $this->companyEdit->getCompanyForm()->fillCustomerGroup($sharedCatalog->getName());
        if ($this->companyEdit->getModalBlock()->isVisible()) {
            $this->companyEdit->getModalBlock()->acceptAlert();
        }
        $this->companyEdit->getFormPageActions()->save();
        return ['company' => $initialCompany, 'catalog' => $sharedCatalog];
    }
}
