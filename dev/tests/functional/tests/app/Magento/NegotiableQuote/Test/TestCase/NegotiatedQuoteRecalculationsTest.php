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
 * 3. As a merchant open a quote, add a discount and send the quote back to the buyer.
 * 4. Open quote in the Storefront and add an address.
 * 5. Send the quote to the merchant.
 * 6. Open the quote in the admin panel and click "Update prices" button.
 * 7. Update product qty in the admin panel.
 * 8. Click "Recalculate quote" button.
 * 9. Select some shipping method and set proposed shipping price.
 * 10. Send quote back to the buyer.
 * 11. Open quote in the Storefront and add new address.
 * 12. Perform assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68180
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class NegotiatedQuoteRecalculationsTest extends \Magento\NegotiableQuote\Test\TestCase\AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Create tax rule.
     *
     * @param string $taxRule
     * @return void
     */
    protected function createTaxRule($taxRule)
    {
        $this->taxRule = $taxRule;
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

    /**
     * Update quote in admin
     *
     * @param array $discount
     * @param array $qtys
     * @param string $shippingPrice
     * @return void
     */
    protected function adminUpdateQuote(array $discount = [], array $qtys = [], $shippingPrice = '')
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        if (!empty($qtys)) {
            $this->negotiableQuoteView->getQuoteDetails()->updateItems($qtys);
            $this->negotiableQuoteView
                ->getQuoteDetails()->recalculateQuote();
        }
        if (!empty($shippingPrice)) {
            $this->negotiableQuoteView
                ->getQuoteDetails()->fillProposedShippingPrice($shippingPrice);
        }
        if (!empty($discount)) {
            $this->negotiableQuoteView
                ->getQuoteDetails()->fillDiscount($discount['discountType'], $discount['discountValue']);
        }
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
    }

    /**
     * Admin update prices.
     *
     * @return void
     */
    protected function adminUpdatePrices()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView
            ->getQuoteDetails()
            ->updatePrices();
        $this->negotiableQuoteView
            ->getQuoteDeclineRestrictionPopup()
            ->confirmDecline();
    }

    /**
     * Update address in Storefront
     *
     * @param Address $address
     * @return void
     */
    protected function frontUpdateAddress(Address $address)
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->clickNewAddress();
        $this->customerAddressEdit->getEditForm()->editCustomerAddress($address);
    }

    /**
     * Test quote negotiation.
     *
     * @param array $productsList
     * @param Customer $customer
     * @param array $quote
     * @param array $messages
     * @param Address $address
     * @param string $taxRule
     * @param string $configData
     * @param array $discount
     * @param array $qtys
     * @param string $shippingPrice
     * @param Address $addressToUpdate
     * @param array $additionalSteps
     * @param array $dataFixtures
     * @return array
     */
    public function test(
        array $productsList,
        Customer $customer,
        array $quote = [],
        array $messages = [],
        Address $address = null,
        $taxRule = null,
        $configData = null,
        array $discount = [],
        array $qtys = [],
        $shippingPrice = '',
        Address $addressToUpdate = null,
        array $additionalSteps = [],
        array $dataFixtures = []
    ) {
        //Preconditions
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
        if ($dataFixtures) {
            foreach ($dataFixtures as $key => $dataFixture) {
                $$key = $this->fixtureFactory->createByCode(
                    $dataFixture['code'],
                    [
                        'dataset' => $dataFixture['dataset'],
                    ]
                );
                $$key->persist();
            }
        }
        if ($taxRule) {
            $this->createTaxRule($taxRule);
        }
        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
            $this->messages = array_merge($messages, [$quote['quote-message']]);
        }
        $products = $this->createProducts($productsList);

        //Steps
        $this->loginCustomer($customer);
        if (!empty($quote)) {
            $this->addToCart($products);
            $this->requestQuote($quote);
        }
        $this->adminUpdateQuote($discount[0]);
        if ($additionalSteps) {
            foreach ($additionalSteps as $additionalStep) {
                $arguments = [];
                foreach (explode(',', $additionalStep['arguments']) as $argument) {
                    $arguments[$argument] = $$argument;
                }
                $this->objectManager->create(
                    $additionalStep['name'],
                    $arguments
                )->run();
            }
        }
        $this->frontUpdateAddress($address);
        $this->frontSend();
        $this->adminUpdatePrices();
        $this->adminUpdateQuote([], $qtys, $shippingPrice);
        $this->frontUpdateAddress($addressToUpdate);

        return [
            'shippingAddress' => $addressToUpdate->getData()
        ];
    }
}
