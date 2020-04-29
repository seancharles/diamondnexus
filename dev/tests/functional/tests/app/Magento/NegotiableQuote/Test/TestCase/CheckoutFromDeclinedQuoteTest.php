<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Fixture\Address;

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
 * 4. Decline newly created quote.
 * 5. Place order using the declined quote.
 * 6. Create invoice.
 * 8. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68127
 * @SuppressWarnings(PHPMD)
 */
class CheckoutFromDeclinedQuoteTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Place order with giftcard on Storefront
     *
     * @return array
     */
    protected function frontPlaceOrderWithDiscount()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->checkout();
        $this->checkoutOnepage->getShippingMethodBlock()->clickContinue();
        if (!empty($this->giftCard['giftCardAccount'])) {
            $this->checkoutOnepage->getQuoteGiftCardBlock()->addGiftCard($this->giftCard['giftCardAccount']->getCode());
        }
        $this->updateData['orderId'] = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return $this->updateData;
    }

    /**
     * Test checkout from declined quote
     *
     * @param array $productsList
     * @param Customer $customer
     * @param array $quote
     * @param array $additionalQuote
     * @param array $messages
     * @param array $steps
     * @param array $updateData
     * @param Address $address
     * @param string $taxRule
     * @param int $tax
     * @param array $marketing
     * @param string $configData
     * @return array
     */
    public function test(
        array $productsList,
        Customer $customer,
        array $quote = [],
        array $additionalQuote = [],
        array $messages = [],
        array $steps = [],
        array $updateData = [],
        Address $address = null,
        $taxRule = null,
        $tax = 0,
        array $marketing = [],
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
        $products = $this->createProducts($productsList);

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

        if (isset($marketing['giftCard'])) {
            /** @var \Magento\GiftCardAccount\Test\TestStep\CreateGiftCardAccountStep $createGiftCardStep */
            $createGiftCardStep = $this->objectManager->create(
                \Magento\GiftCardAccount\Test\TestStep\CreateGiftCardAccountStep::class,
                [
                    'giftCardAccount' => $marketing['giftCard']
                ]
            );
            $this->giftCard = $createGiftCardStep->run();
        }

        if (isset($marketing['salesRule'])) {
            $this->salesRule = $marketing['salesRule'];
            /** @var \Magento\SalesRule\Test\TestStep\CreateSalesRuleStep $createSalesRuleStep */
            $createSalesRuleStep = $this->objectManager->create(
                \Magento\SalesRule\Test\TestStep\CreateSalesRuleStep::class,
                ['salesRule' => $marketing['salesRule']]
            );
            $this->salesRule = $createSalesRuleStep->run();
        }

        $invoiceIds = [];
        $qtys = isset($updateData['qtys']) ? $updateData['qtys'] : [1, 2];

        //%isolation% not working on arrays
        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
            $this->messages = array_merge($messages, [$quote['quote-message']]);
        }

        // Steps
        $this->loginCustomer($customer);
        $this->addToCart($products);
        $this->requestQuote($quote);
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $additionalQuote['quote-name'] .= time();
        $this->addToCart($products);
        $this->requestQuote($additionalQuote);
        $this->additionalQuote = $additionalQuote;

        if ($address) {
            $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
            $this->addAddress($address);
        }

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }

        return [
            'ids' => [ 'invoiceIds' => $invoiceIds],
            'orderId' => $this->updateData['orderId'],
            'giftCardAccount' => $this->giftCard['giftCardAccount'],
            'tax' => $tax,
            'products' => $products,
            'qtys' => $qtys,
            'salesRule' => $this->salesRule['salesRule']
        ];
    }
}
