<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Bundle\Test\Fixture\BundleProduct;

/**
 * Preconditions:
 * 1. Apply configuration settings.
 * 2. Create customer.
 * 3. Create company.
 * 4. Create products.
 *
 * Steps:
 * 1. Login as a customer.
 * 2. Request a quote.
 * 3. Login to the admin panel.
 * 4. Add one complex product and one fake product by SKU.
 * 5. Configure complex product.
 * 6. Remove products that failed validation.
 * 7. Save quote as draft.
 * 8. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68147, @ZephyrId MAGETWO-68151, @ZephyrId MAGETWO-68069
 * @SuppressWarnings(PHPMD)
 */
class AddComplexProductsToQuoteTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Request a quote
     *
     * @param array $quote
     * @return array
     */
    protected function requestQuote(array $quote)
    {
        $this->cartPage->open();
        $this->cartPage->getRequestQuote()->requestQuote();
        $this->cartPage->getRequestQuotePopup()->fillForm($quote);
        $this->cartPage->getRequestQuotePopup()->submitQuote();

        return [];
    }

    /**
     * Save quote as draft in the admin panel
     *
     * @return array
     */
    protected function saveQuoteAsDraft()
    {
        $this->negotiableQuoteView->getQuoteDetailsActions()->saveAsDraft();
        $this->negotiableQuoteView->getQuoteDeclineRestrictionPopup()->confirmDecline();

        return [];
    }

    /**
     * Send quote to buyer
     *
     * @return array
     */
    protected function adminSend()
    {
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
        $this->negotiableQuoteView->getQuoteDeclineRestrictionPopup()->confirmDecline();

        return [];
    }

    /**
     * Test add complex products to quote
     *
     * @param CatalogProductSimple $simpleProduct
     * @param GroupedProduct $groupedProduct
     * @param Customer $customer
     * @param BundleProduct $bundleProduct
     * @param array $quote
     * @param array $messages
     * @param array $steps
     * @param array $updateData
     * @param string $taxRule
     * @param int $tax
     * @param string $configData
     * @param int $qty
     * @return array
     */
    public function test(
        CatalogProductSimple $simpleProduct,
        Customer $customer,
        BundleProduct $bundleProduct = null,
        GroupedProduct $groupedProduct = null,
        array $quote = [],
        array $messages = [],
        array $steps = [],
        array $updateData = [],
        $taxRule = null,
        $tax = 0,
        $configData = null,
        $qty = null
    ) {
        $products = [];
        $this->configData = $configData;
        $this->skuArray = [];
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $customer->getEmail(),
                ],
            ]
        );
        $company->persist();
        $simpleProduct->persist();
        $products[] = $simpleProduct;

        if ($groupedProduct) {
            $groupedProduct->persist();
            $this->skuArray[] = $groupedProduct->getSku();
            $products[] = $groupedProduct;
        }
        if ($bundleProduct) {
            $bundleProduct->persist();
            $this->skuArray[] = $bundleProduct->getSku();
            $products[] = $bundleProduct;
        }
        $this->taxRule = $taxRule;
        $this->tax = $tax;
        if ($taxRule) {
            /** @var \Magento\Tax\Test\TestStep\CreateTaxRuleStep $createTaxRuleStep */
            $createTaxRuleStep = $this->objectManager->create(
                \Magento\Tax\Test\TestStep\CreateTaxRuleStep::class,
                [
                    'taxRule' => $taxRule
                ]
            );
            $createTaxRuleStep->cleanup();
            $createTaxRuleStep->run();
        }

        $this->skuArray[] = $updateData['fakeSku'];
        $this->updateData = $updateData;
        $this->qty = $qty;
        //%isolation% not working on arrays
        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
            $this->messages = array_merge($messages, [$quote['quote-message']]);
        }

        // Steps
        $this->loginCustomer($customer);
        $this->addToCart([$simpleProduct]);
        $this->requestQuote($quote);

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }

        return [
            'simpleProduct' => $simpleProduct,
            'groupedProduct' => $groupedProduct ? $groupedProduct : null,
            'bundleProduct' => $bundleProduct ? $bundleProduct : null,
            'historyLog' => isset($this->updateData['historyLog']) ? $this->updateData['historyLog'] : null,
            'quote' => $this->quote
        ];
    }
}
