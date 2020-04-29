<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Apply configuration settings.
 * 2. Create tax zones and rates.
 * 3. Create customer.
 * 4. Create company.
 * 5. Create 2 products.
 *
 * Steps:
 * 1. Login as a customer.
 * 2. Add products to cart.
 * 3. Request a quote and specify shipping address.
 * 4. Login to the admin panel.
 * 5. Disable one of the products.
 * 6. Open the quote.
 * 7. Send the quote to buyer.
 * 8. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68253
 */
class CheckQuoteAfterDisablingProductTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Loader.
     *
     * @var string
     */
    private $loader = '.loading-mask';

    /**
     * Test.
     *
     * @param Customer $customer
     * @param array $productsList
     * @param array $quote
     * @param array $steps
     * @param string $taxRule
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        array $productsList,
        array $quote = [],
        array $steps = [],
        $taxRule = null,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->createTaxRules($taxRule);
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
        $this->loginCustomer($customer);
        $products = $this->createProducts($productsList);
        $productName = '';
        $sku = '';

        if (!empty($products[0])) {
            $productName = $products[0]->getName();
            $sku = $products[0]->getSku();
        }

        $this->addToCart($products);

        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
        }

        $this->requestQuote($quote);

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }

        $this->quoteFrontendView->getQuoteDetails()->waitForElementNotVisible($this->loader);
        $this->quoteFrontendView->getQuoteDetails()->send();

        $this->disableProduct($productName);

        return [
            'quote' => $this->quote,
            'negotiableQuoteIndex' => $this->negotiableQuoteGrid,
            'negotiableQuoteEdit' => $this->negotiableQuoteView,
            'quoteFrontendGrid' => $this->quoteFrontendGrid,
            'quoteFrontendEdit' => $this->quoteFrontendView,
            'sku' => $sku
        ];
    }

    /**
     * Creates all necessary tax rules.
     *
     * @param string $taxRule
     * @return void
     */
    private function createTaxRules($taxRule)
    {
        $this->taxRule = $taxRule;
        /** @var \Magento\Tax\Test\TestStep\CreateTaxRuleStep $createTaxRuleStep */
        $createTaxRuleStep = $this->objectManager->create(
            \Magento\Tax\Test\TestStep\CreateTaxRuleStep::class,
            ['taxRule' => $taxRule]
        );
        $createTaxRuleStep->cleanup();
        $taxRuleData = $createTaxRuleStep->run()['taxRule'];
        $taxClass = $taxRuleData->getDataFieldConfig('tax_product_class')['source']->getFixture()[0];
        $taxClassConfig = $this->fixtureFactory->createByCode(
            'configData',
            ['dataset' => 'shipping_tax_class_shipping',
                'data' => [
                    'tax/classes/shipping_tax_class' => [
                        'value' => $taxClass->getId(),
                        'label' => $taxClass->getClassName()
                    ]
                ]
            ]
        );
        $taxClassConfig->persist();
    }

    /**
     * Disable product.
     *
     * @param string $productName
     * @throws \Exception
     * @return void
     */
    private function disableProduct($productName)
    {
        $this->catalogProductIndex->open();
        $filter = ['name' => $productName];
        $this->catalogProductIndex->getProductGrid()->searchAndOpen($filter);
        $this->catalogProductEdit->getDisableProduct()->setProductDisabled();
        $this->catalogProductEdit->getFormPageActions()->saveAndClose();
        $this->catalogProductIndex->getProductGrid()->waitForElementNotVisible($this->loader);
    }
}
