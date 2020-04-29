<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Apply configuration settings.
 * 2. Create customer.
 * 3. Create company.
 * 4. Create products.
 *
 * Steps:
 * 1. Login as a customer.
 * 2. Request several quotes.
 * 3. Login to the admin panel.
 * 4. Decline the created quotes.
 * 5. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68144
 */
class MassQuoteDeclineTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Quotes array to be declined.
     *
     * @var array
     */
    private $quotesToDecline;

    /**
     * Mass decline quotes.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function adminMassDecline()
    {
        $this->negotiableQuoteGrid->open();
        $filter = [];
        foreach ($this->quotesToDecline as $quote) {
            $filter[] = ['quote_name' => $quote['quote-name']];
        }
        $this->negotiableQuoteGrid->getGrid()->massaction($filter, 'Decline');
        if (isset($this->messages['decline-comment'])) {
            $this->negotiableQuoteGrid->getDeclinePopupBlock()
                ->fillDeclineReason($this->messages['decline-comment'])
                ->confirmDecline();
        }

        return [];
    }

    /**
     * Open quote in the admin panel.
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function adminOpenQuote()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);

        return [];
    }

    /**
     * Test quotes mass decline.
     *
     * @param array $productsList
     * @param Customer $customer
     * @param array $quote
     * @param array $steps
     * @param array $messages
     * @param string|null $configData
     * @param int|null $quotesToSubmit
     * @param int|null $declinePopupMessage
     * @return array
     */
    public function test(
        array $productsList,
        Customer $customer,
        array $quote = [],
        array $steps = [],
        array $messages = [],
        $configData = null,
        $quotesToSubmit = null,
        $declinePopupMessage = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $this->customer = $customer;
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

        // Preconditions
        $products = $this->createProducts($productsList);
        $this->products = $products;
        $this->messages = $messages;

        // Steps
        $this->loginCustomer($customer);

        for ($i = 1; $i <= $quotesToSubmit; $i++) {
            $quote[$i] = [
                    'quote-name' => 'name' . time(),
                    'quote-message' => 'message' . time()
                ];
            $this->addToCart($products);
            $this->requestQuote($quote[$i]);
        }
        $this->quotesToDecline = $quote;
        $this->quote = $quote[1];

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }

        return [
            'quote' => $this->quote,
            'declinePopupMessage' => $declinePopupMessage
        ];
    }
}
