<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogConfigure;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogIndex;
use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Company\Test\Fixture\Company;
use Magento\SharedCatalog\Test\Page\Adminhtml\SharedCatalogCompany;

/**
 * Class AssignCompanyStep
 * Assign company step
 */
class AssignCompanyStep implements TestStepInterface
{
    /**
     * @var SharedCatalogIndex $sharedCatalogIndex
     */
    protected $sharedCatalogIndex;

    /**
     * @var SharedCatalogCompany $sharedCatalogCompany
     */
    protected $sharedCatalogCompany;

    /**
     * Shared catalog.
     *
     * @var SharedCatalog
     */
    protected $sharedCatalog;

    /**
     * Catalog Product.
     *
     * @var Company
     */
    protected $company;

    /**
     * @constructor
     * @param SharedCatalogIndex $sharedCatalogIndex
     * @param SharedCatalogCompany $sharedCatalogCompany
     * @param SharedCatalog $sharedCatalog
     * @param Company $company
     */
    public function __construct(
        SharedCatalogIndex $sharedCatalogIndex,
        SharedCatalogCompany $sharedCatalogCompany,
        SharedCatalog $sharedCatalog,
        Company $company
    ) {
        $this->sharedCatalogIndex = $sharedCatalogIndex;
        $this->sharedCatalogCompany = $sharedCatalogCompany;
        $this->sharedCatalog = $sharedCatalog;
        $this->company = $company;
    }

    /**
     * Assign company to shared catalog.
     *
     * @return array
     */
    public function run()
    {
        $this->sharedCatalogIndex->open();
        $this->sharedCatalogIndex->getGrid()->search(['name' => $this->sharedCatalog->getName()]);
        $sharedCatalogId = $this->sharedCatalogIndex->getGrid()->getFirstItemId();
        $this->sharedCatalogIndex->getGrid()->openCompanies($sharedCatalogId);
        $this->sharedCatalogCompany->getCompanyGrid()->search(['company_name' => $this->company->getCompanyName()]);
        $companyId = $this->sharedCatalogCompany->getCompanyGrid()->getFirstItemId();
        $this->sharedCatalogCompany->getCompanyGrid()->assignCatalog($companyId);
        if ($this->sharedCatalogCompany->getModalBlock()->isVisible()) {
            $this->sharedCatalogCompany->getModalBlock()->acceptAlert();
        }
        $this->sharedCatalogCompany->getPageActions()->save();
    }
}
