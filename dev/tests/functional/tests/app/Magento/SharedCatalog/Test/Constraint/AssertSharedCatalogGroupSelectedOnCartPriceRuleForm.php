<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\SalesRule\Test\Fixture\SalesRule;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\SharedCatalog\Test\Block\Adminhtml\Promo\Quote\Edit\Section\RuleInformation;
use Magento\SalesRule\Test\Page\Adminhtml\PromoQuoteNew;

/**
 * Assert that customer group is selected on cart price rule page.
 */
class AssertSharedCatalogGroupSelectedOnCartPriceRuleForm extends AbstractConstraint
{
    /**
     * Assert that customer group is not on cart price rule page.
     *
     * @param PromoQuoteNew $promoQuoteNew
     * @param SharedCatalog $sharedCatalog
     * @param SalesRule $cartPriceRule
     * @return void
     */
    public function processAssert(
        PromoQuoteNew $promoQuoteNew,
        SharedCatalog $sharedCatalog,
        SalesRule $cartPriceRule
    ) {
        $promoQuoteNew->open();
        $promoQuoteNew->getSalesRuleForm()->openSection('rule_information');
        $promoQuoteNew->getSalesRuleForm()->fill($cartPriceRule);
        /** @var RuleInformation $ruleInformationTab */
        $ruleInformationTab = $promoQuoteNew->getSalesRuleForm()->getSection('rule_information');
        \PHPUnit\Framework\Assert::assertTrue(
            $ruleInformationTab->isVisibleSharedCatalogGroup($sharedCatalog),
            "Shared catalog group {$sharedCatalog->getName()} not selected in cart price rule page."
        );
    }

    /**
     * Success assert of customer group selected on cart price rule page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Shared catalog group is selected on cart price rule page.';
    }
}
