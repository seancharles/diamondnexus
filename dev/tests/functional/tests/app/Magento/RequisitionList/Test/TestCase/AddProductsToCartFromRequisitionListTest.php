<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create simple products.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Create two requisition lists.
 * 3. Add products to the created requisition lists.
 * 4. Open first requisition list and add products from it to the shopping cart.
 * 5. Open second requisition list, add it's products to the shopping cart (select "Merge" in the popup).
 * 6. Perform assertions.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68217
 */
class AddProductsToCartFromRequisitionListTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Add products to cart from requisition list.
     *
     * @param array $requisitionList
     * @return void
     */
    protected function addProductsToCart(array $requisitionList)
    {
        $this->requisitionListGrid->open();
        $this->requisitionListGrid->getRequisitionListGrid()->openRequisitionListByName($requisitionList['name']);
        $this->requisitionListView->getRequisitionListContent()->selectProducts();
        $this->requisitionListView->getRequisitionListContent()->addProductsToCart();
        $this->requisitionListView->getRequisitionListPopup()->confirm();
    }

    /**
     * Add products to cart from the requisition lists.
     *
     * @param Customer $customer
     * @param array $productsList
     * @param array $productListForSecondRl
     * @param array $requisitionList
     * @param array $secondRequisitionList
     * @param array $cart
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        array $productsList,
        array $productListForSecondRl,
        array $requisitionList,
        array $secondRequisitionList,
        array $cart,
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
        $productsForSecondRl = $this->createProducts($productListForSecondRl);
        $this->loginCustomer($customer);
        $cart['data']['items'] = ['products' => array_merge($products, $productsForSecondRl)];
        $requisitionList = $this->createRequisitionList($requisitionList);
        $secondRequisitionList = $this->createRequisitionList($secondRequisitionList);
        $this->addToRequisitionList($products, $requisitionList);
        $this->addToRequisitionList($productsForSecondRl, $secondRequisitionList);
        $this->addProductsToCart($requisitionList);
        $this->addProductsToCart($secondRequisitionList);

        return [
            'products' => array_merge($products, $productsForSecondRl),
            'cart' => $this->fixtureFactory->createByCode('cart', $cart)
        ];
    }
}
