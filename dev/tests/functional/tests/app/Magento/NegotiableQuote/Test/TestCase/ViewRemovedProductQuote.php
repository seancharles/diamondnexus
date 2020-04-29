<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Mtf\ObjectManager;
use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company.
 * 3. Create products.
 *
 * Steps:
 * 1. Login as a customer to the SF.
 * 2. Add products to quote.
 * 3. Request a quote.
 * 4. Delete product added to quote.
 * 5. Perform assertions.
 *
 * @group    NegotiableQuote
 * @ZephyrId MAGETWO-76040
 */
class ViewRemovedProductQuote extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * @param string   $configData
     * @param array    $products
     * @param Customer $customer
     * @param string   $company
     * @return array
     */
    public function test(
        string $configData,
        array $products,
        Customer $customer,
        string $company
    ) {
        $this->configData = $configData;
        $this->products = $products;

        // Enable B2B Features: Company and Negotiable quotes.
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();

        // Created products
        $createdProducts = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $this->products]
        )->run()['products'];

        // Create company with company admin
        $companyAdmin = $this->objectManager->create(
            \Magento\NegotiableQuote\Test\TestStep\CreateCompanyAdminStep::class,
            [
                'customer' => $customer,
                'company'  => $company,
            ]
        )->run()['customer'];

        // Login on SF
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $companyAdmin]
        )->run();

        // Add products into shopping cart
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $createdProducts]
        )->run();

        // Request negotiable quote
        $this->objectManager->create(
            \Magento\NegotiableQuote\Test\TestStep\RequestQuoteStep::class
        )->run()['quote'];

        // Delete 1-st product
        $productToDelete = current($createdProducts);
        $productsToDelete = [
            ['sku' => $productToDelete->getSku()],
        ];
        $this->catalogProductIndex->open();
        $this->catalogProductIndex->getProductGrid()->massaction($productsToDelete, 'Delete', true);

        return [
            'deletedProducts' => [$productToDelete],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        $this->logoutCustomerOnFrontendStep->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
