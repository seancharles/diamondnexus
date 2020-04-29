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
 * 2. Create customer.
 * 3. Create company.
 * 4. Create product.
 *
 * Steps
 * 1. Login as a customer.
 * 2. Add product to cart.
 * 3. Request a quote and specify shipping address.
 * 4. Login to the admin panel.
 * 5. Open the quote.
 * 6. Select shipping method.
 * 7. Set proposed shipping price.
 * 8. Send the quote to buyer.
 * 9. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68267
 */
class SpecifyShippingMethodAndCustomShippingPriceTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @var string
     */
    private $proposedShippingPrice = '15';

    /**
     * Test.
     *
     * @param Customer $customer
     * @param CatalogProductSimple $simpleProduct
     * @param array $quote
     * @param array $steps
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        CatalogProductSimple $simpleProduct,
        array $quote = [],
        array $steps = [],
        $configData = null
    ) {
        $this->configData = $configData;
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
        $this->loginCustomer($customer);
        $simpleProduct->persist();
        $this->addToCart([$simpleProduct]);

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

        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetails()->fillProposedShippingPrice($this->proposedShippingPrice);
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();

        return [
            'quote' => $this->quote,
            'negotiableQuoteIndex' => $this->negotiableQuoteGrid,
            'negotiableQuoteEdit' => $this->negotiableQuoteView,
            'proposedShippingPrice' => $this->proposedShippingPrice,
            'quoteFrontendGrid' => $this->quoteFrontendGrid,
            'quoteFrontendEdit' => $this->quoteFrontendView
        ];
    }
}
