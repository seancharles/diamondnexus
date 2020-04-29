<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Fixture\Company;

/**
 * Preconditions:
 * 1. Create company.
 * 2. Create 2 custom Shared catalogs
 *
 * Steps:
 * 1. Open Shared catalog list
 * 2. Find first shared catalog in list
 * 3. Choose Companies from action list
 * 4. Filter list to find Company
 * 5. Click Assign
 * 6. Click Save
 * 7. Find second Shared catalog in list
 * 8. Open Companies list
 * 9. Find Company
 * 10. Make assertions
 *
 * @group SharedCatalog
 * @ZephyrId MAGETWO-67974, @ZephyrId MAGETWO-67973
 */
class AssignCompanyTest extends Injectable
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
     * @var SharedCatalogCompany $sharedCatalogCompany
     */
    private $sharedCatalogCompany;

    /**
     * Inject pages.
     *
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @return void
     */
    public function __inject(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCompany $sharedCatalogCompany
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
    }

    /**
     * Create SharedCatalog.
     *
     * @param SharedCatalog $sharedCatalog
     * @param SharedCatalog $sharedCatalog2
     * @param Company $company
     * @return array
     */
    public function test(SharedCatalog $sharedCatalog, SharedCatalog $sharedCatalog2, Company $company)
    {
        $company->persist();
        $sharedCatalog->persist();
        $sharedCatalog2->persist();
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $company->getCompanyName()]);
        $companyId = $this->sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $this->sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);
        if ($this->sharedCatalogCompany->getModalBlock()->isVisible()) {
            $this->sharedCatalogCompany->getModalBlock()->acceptAlert();
        }
        $this->sharedCatalogCompany->getPageActions()->save();

        $this->sharedCatalogIndex->getGrid()->search(['name' => $sharedCatalog2->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $company->getCompanyName()]);

        return [
            'sharedCatalogCompany' => $this->sharedCatalogCompany,
            'sharedCatalog' => $sharedCatalog,
        ];
    }
}
