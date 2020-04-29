<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SharedCatalog\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert prices of product in shared catalog on storefront.
 */
class AssertProductPricesOnCustomWebsiteOnStorefront extends AbstractConstraint
{
    /**
     * Assert prices of product in shared catalog on storefront.
     *
     * @param CatalogProductView $productView
     * @param Customer $customer
     * @param Website $website
     * @param BrowserInterface $browser
     * @param array $products
     * @param array $customPrices
     * @return void
     */
    public function processAssert(
        CatalogProductView $productView,
        Customer $customer,
        Website $website,
        BrowserInterface $browser,
        array $products,
        array $customPrices
    ) {
        $this->loginCustomer($customer);

        foreach ($customPrices as $key => $customPrice) {
            $product = $products[$key];
            $browser->open(
                $_ENV['app_frontend_url'] . 'websites/' . $website->getCode() . '/' . $product->getUrlKey() . '.html'
            );
            \PHPUnit\Framework\Assert::assertEquals(
                str_replace('$', '', $customPrice['new_price']),
                $productView->getGroupedProductViewBlock()->getPriceBlock()->getPrice(),
                'Price for product \'' . $product->getName() . '\' is incorrect.'
            );
        }
    }

    /**
     * Login customer on storefront.
     *
     * @param Customer $customer
     * @return void
     */
    private function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product prices are correct.';
    }
}
