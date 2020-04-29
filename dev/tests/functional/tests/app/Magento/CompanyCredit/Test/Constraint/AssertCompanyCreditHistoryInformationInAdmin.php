<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\Constraint;

use Magento\Company\Test\Fixture\Company;
use Magento\Company\Test\Page\Adminhtml\CompanyIndex;
use Magento\Company\Test\Page\Adminhtml\CompanyEdit;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert is company credit balance history valid.
 */
class AssertCompanyCreditHistoryInformationInAdmin extends AbstractConstraint
{
    /**
     * Assert company credit balance history is valid.
     *
     * @param CompanyIndex $companyIndex
     * @param CompanyEdit $companyEdit
     * @param Company $company
     * @param array $historyData
     * @return void
     */
    public function processAssert(
        CompanyIndex $companyIndex,
        CompanyEdit $companyEdit,
        Company $company,
        array $historyData
    ) {
        $companyIndex->open();
        $companyIndex->getGrid()->searchAndOpen(['company_name' => $company->getCompanyName()]);
        $companyEdit->getCompanyForm()->openSection('company_credit');
        foreach ($historyData as $operationType => $historyDataItem) {
            $companyEdit->getCreditHistoryGrid()->search(['operation' => $operationType]);

            foreach ($historyDataItem as $operationDataItem) {
                $expectedFieldValue = $operationDataItem['value'];
                if (is_array($expectedFieldValue)) {
                    $expectedFieldValue = implode("\n", $expectedFieldValue);
                }
                $fieldValue = $companyEdit->getCreditHistoryGrid()->getFirstRowGridValue($operationDataItem['label']);

                \PHPUnit\Framework\Assert::assertEquals(
                    $expectedFieldValue,
                    $fieldValue,
                    sprintf('%s field value is incorrect.', $operationDataItem['label'])
                );
            }
        }

        $companyEdit->getCreditHistoryGrid()->resetFilter();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'History log values are correct.';
    }
}
