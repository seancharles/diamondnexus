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
 * 3. Add second shipping address.
 * 4. Login to the admin panel.
 * 5. Add discount to the quote and send it back to the buyer.
 * 6. Go to the SF and place order with the second address.
 * 7. Perform assertions.
 *
 * @group NegotiableQuote.
 * @ZephyrId MAGETWO-68225, @ZephyrId MAGETWO-68087
 *
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class CheckoutWithDifferentAddressTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Update address.
     *
     * @var Address
     */
    private $updateAddress;

    /**
     * Add address.
     *
     * @param Address $address
     * @return void
     */
    protected function frontAddAddress(Address $address)
    {
        $this->customerAddressEdit->open();
        $this->customerAddressEdit->getEditForm()->editCustomerAddress($address);
    }

    /**
     * Place order on Storefront with different address.
     *
     * @return array
     */
    protected function frontPlaceOrderWithDifferentAddress()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->checkout();
        $this->checkoutOnepage->getShippingAddressBlock()->selectShippingAddress($this->address->getRegionId());
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            [
                'shipping' => $this->shipping
            ]
        )->run();
        $this->updateData['orderId'] = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return [];
    }

    /**
     * Storefront change billing address.
     *
     * @return array
     */
    protected function frontChangeBillingAddress()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->checkout();
        $this->checkoutOnepage->getQuotePaymentBlock()->selectAddNewAddressOption();
        $this->checkoutOnepage->getBillingBlock()->fillBilling($this->updateAddress);
        return [];
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
     * Test place order with different address.
     *
     * @param array $productsList
     * @param Customer $customer
     * @param array $quote
     * @param array $messages
     * @param array $steps
     * @param array $updateData
     * @param array $shipping
     * @param array $prices
     * @param Address $address
     * @param Address $updateAddress
     * @param string $taxRule
     * @param string $configData
     * @param string $grandTotal
     * @param string $tax
     * @return array
     */
    public function test(
        array $productsList,
        Customer $customer,
        array $quote = [],
        array $messages = [],
        array $steps = [],
        array $updateData = [],
        array $shipping = [],
        array $prices = [],
        Address $address = null,
        Address $updateAddress = null,
        $taxRule = null,
        $configData = null,
        $grandTotal = null,
        $tax = null
    ) {
        // Preconditions
        $this->createTaxRules($taxRule);
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

        $expirationDate = new \DateTime('+30 days');
        if (isset($updateData['expirationDate'])) {
            $expirationDate = new \DateTime($updateData['expirationDate']);
        }
        $updateData['expirationDate'] = $expirationDate;
        $this->shipping = $shipping;
        $this->updateData = $updateData;

        //%isolation% not working on arrays
        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
            $this->messages = array_merge($messages, [$quote['quote-message']]);
        }
        $products = $this->createProducts($productsList);
        $this->products = $products;

        // Steps
        $this->loginCustomer($customer);
        if (!empty($quote)) {
            $this->addToCart($products);
            $this->requestQuote($quote);
        }
        $this->address = $address;
        if ($address) {
            $this->frontAddAddress($address);
        }
        $this->updateAddress = $updateAddress;

        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }
        $orderId = null;
        if (isset($this->updateData['orderId'])) {
            $orderId = $this->updateData['orderId'];
        }
        return [
            'quote' => $quote,
            'orderId' =>  $orderId,
            'prices' => $prices,
            'grandTotal' => $grandTotal,
            'taxTotal' => $tax
        ];
    }

    /**
     * Logout customer from Storefront account.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->logoutCustomerOnFrontendStep->run();
        if ($this->taxRule) {
            $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class, [])->run();
        }
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'shipping_tax_class_rollback']
        )->run();
    }
}
