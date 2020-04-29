<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;
use Magento\CatalogRule\Test\Page\Adminhtml\CatalogRuleNew;
use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\CatalogRule\Test\Fixture\CatalogRule;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\CatalogRule\Test\Block\Adminhtml\Promo\Catalog\Edit\Section\RuleInformation;

/**
 * Assert that customer group selected on catalog price rule page.
 */
class AssertCustomerGroupSelectedOnCatalogPriceRuleForm extends AbstractConstraint
{
    /**
     * @param CatalogRuleIndex $catalogRuleIndex
     * @param CatalogRuleNew $catalogRuleNew
     * @param CustomerGroup $customerGroup
     * @param CatalogRule $catalogPriceRule
     * @return void
     */
    public function processAssert(
        CatalogRuleIndex $catalogRuleIndex,
        CatalogRuleNew $catalogRuleNew,
        CustomerGroup $customerGroup,
        CatalogRule $catalogPriceRule
    ) {
        $catalogRuleIndex->open();
        $catalogRuleIndex->getGridPageActions()->addNew();
        $catalogRuleNew->getEditForm()->openSection('rule_information');

        /** @var RuleInformation $ruleInformationSection */
        $ruleInformationSection = $catalogRuleNew->getEditForm()->getSection('rule_information');
        $catalogRuleNew->getEditForm()->fill($catalogPriceRule);
        \PHPUnit\Framework\Assert::assertTrue(
            $ruleInformationSection->isVisibleCustomerGroup($customerGroup),
            "Customer group {$customerGroup->getCustomerGroupCode()} not selected in catalog price rule page."
        );
    }

    /**
     * Success assert of customer group selected on catalog price rule page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group selected on catalog price rule page.';
    }
}
