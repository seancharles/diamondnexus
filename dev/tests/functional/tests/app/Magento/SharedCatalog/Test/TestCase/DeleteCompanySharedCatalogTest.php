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
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCreate;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create shared catalog.
 * 2. Create company.
 * 3. Set Public type for Default catalog.
 *
 * Steps:
 * 1. Login to Admin Panel.
 * 2. Go to Customers > Companies.
 * 3. Open created company.
 * 4. Change customer group to the group related to new shared catalog.
 * 5. Save company.
 * 6. Delete shared catalog.
 * 7. Perform all assertions.
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-67971
 */
class DeleteCompanySharedCatalogTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    private $sharedCatalogIndex;

    /**
     * @var SharedCatalogCreate $sharedCatalogCreate
     */
    private $sharedCatalogCreate;

    /**
     * @var CompanyIndex
     */
    private $companyIndex;

    /**
     * @var CompanyEdit
     */
    private $companyEdit;

    /**
     * Perform needed injections.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCreate $sharedCatalogCreate
     * @return void
     */
    public function __inject(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCreate $sharedCatalogCreate
    ) {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCreate = $sharedCatalogCreate;
    }

    /**
     * Delete shared catalog and check company.
     *
     * @param Company $initialCompany
     * @param SharedCatalog $sharedCatalog
     * @return array
     */
    public function test(Company $initialCompany, SharedCatalog $sharedCatalog)
    {
        $sharedCatalog->persist();
        $initialCompany->persist();

        /*  We change type of 'Default' catalog to 'Public' to be sure
            that company assigned to $sharedCatalog will be reassigned to the 'General' customer group.
            Otherwise we cannot be sure which customer group will be assigned to the company
            because some other tests change Public catalog. */
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->resetFilter();
        $this->sharedCatalogIndex->getGrid()->search(['name' => 'Default']);
        $this->sharedCatalogIndex->getGrid()->openEdit($this->sharedCatalogIndex->getGrid()->getFirstItemId());
        $this->sharedCatalogCreate->getSharedCatalogForm()->setType('Public');
        if ($this->sharedCatalogCreate->getModalBlock()->isVisible()) {
            $this->sharedCatalogCreate->getModalBlock()->acceptAlert();
            $this->sharedCatalogCreate->getFormPageActions()->save();
        }

        $filter = ['company_name' => $initialCompany->getCompanyName()];

        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);

        $this->companyEdit->getCompanyForm()->fillCustomerGroup($sharedCatalog->getName());
        if ($this->companyEdit->getModalBlock()->isVisible()) {
            $this->companyEdit->getModalBlock()->acceptAlert();
        }
        $this->companyEdit->getFormPageActions()->save();

        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->searchAndSelect(['name' => $sharedCatalog->getName()]);
        $this->sharedCatalogIndex->getGrid()->clickMassDelete();
        $this->sharedCatalogIndex->getModalBlock()->acceptAlert();

        return ['company' => $initialCompany];
    }
}
