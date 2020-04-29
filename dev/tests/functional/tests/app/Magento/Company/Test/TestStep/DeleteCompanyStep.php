<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Company\Test\TestStep;

use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;

/**
 * Class DeleteCompanyStep.
 * Delete company.
 */
class DeleteCompanyStep implements \Magento\Mtf\TestStep\TestStepInterface
{
    /**
     * @var CompanyIndex
     */
    private $companyIndex;

    /**
     * @var CompanyEdit
     */
    private $companyEdit;

    /**
     * @var string
     */
    private $companyName;

    /**
     * DeleteCompanyStep constructor.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param string $companyName
     */
    public function __construct(CompanyIndex $companyIndex, CompanyEdit $companyEdit, $companyName)
    {
        $this->companyIndex = $companyIndex;
        $this->companyEdit = $companyEdit;
        $this->companyName = $companyName;
    }

    /**
     * Delete company.
     */
    public function run()
    {
        $filter = ['company_name' => $this->companyName];
        $this->companyIndex->open();
        $this->companyIndex->getGrid()->searchAndOpen($filter);
        $this->companyEdit->getFormPageActions()->delete();
        $this->companyEdit->getModalBlock()->acceptAlert();
    }
}
