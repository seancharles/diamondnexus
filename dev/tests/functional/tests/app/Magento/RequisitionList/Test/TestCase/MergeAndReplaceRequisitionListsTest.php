<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestCase;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Preconditions:
 * 1. Login as a customer on the Storefront.
 * 2. Create requisition lists.
 * 3. Add simple and configurable products to requisition lists.
 *
 * Steps:
 * 1. Login to the admin panel.
 * 2. Disable a simple product.
 * 3. Set QTY = 1 for the second simple product.
 * 4. Delete variations for a configurable product.
 * 5. Add products from the first requisition list to the shopping cart.
 * 6. Merge products from the second requisition list with the shopping cart.
 * 7. Replace products from the shopping cart with products from the third requisition list.
 * 8. Perform assertions.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68218
 */
class MergeAndReplaceRequisitionListsTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Create order from requisition list with problem products.
     *
     * @param Customer $customer
     * @param ConfigurableProduct $updateProduct
     * @param array $productsList
     * @param array $requisitionList
     * @param int $requisitionListsNumber
     * @param int $productInCartIndex
     * @param float|int $subtotal
     * @param bool $withReplace
     * @param string $configData
     * @return array
     */
    public function test(
        Customer $customer,
        ConfigurableProduct $updateProduct,
        array $productsList,
        array $requisitionList,
        $requisitionListsNumber,
        $productInCartIndex,
        $subtotal,
        $withReplace = false,
        $configData = null
    ) {
        //Preconditions
        $this->configData = $configData;
        $this->objectManager->create(
            'Magento\Config\Test\TestStep\SetupConfigurationStep',
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $updateProduct->persist();
        $fields = [];
        $fields['configurable_attributes_data']['source'] =
            $updateProduct->getDataFieldConfig('configurable_attributes_data')['source'];
        $fields['configurable_attributes_data']['value'] = $updateProduct->getData()['configurable_attributes_data'];
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
        $this->loginCustomer($customer);
        $lists = [];
        for ($i = 0; $i < $requisitionListsNumber; $i++) {
            $lists[$i] = $this->createRequisitionList($requisitionList);
        }
        $this->addToRequisitionList([$products[0], $products[1]], $lists[0]);
        $this->addToRequisitionList([$products[1], $products[2]], $lists[1]);
        if ($withReplace) {
            $this->addToRequisitionList([$products[2], $products[3]], $lists[2]);
        }

        //Steps
        $this->updateProduct($products[0], 'rl_disable_product');
        $this->updateProduct($products[1], 'rl_product_with_qty_1');
        $filter = ['sku' => $products[2]->getSku()];
        $this->productIndex->open();
        $this->productIndex->getProductGrid()->searchAndOpen($filter);
        $this->productEdit->getProductForm()->openSection('variations');
        $variationsTab = $this->productEdit->getProductForm()->getSection('variations');
        $variationsTab->setFieldsData($fields);
        $this->productEdit->getFormPageActions()->save();
        $this->addProductsToCartFromList($lists[0]);
        $this->addProductsToCartFromList($lists[1]);
        $this->requisitionListView->getRequisitionListPopup()->confirm();
        if ($withReplace) {
            $this->addProductsToCartFromList($lists[2]);
            $this->requisitionListView->getRequisitionListPopup()->replace();
        }

        return [
            'products' => [$products[$productInCartIndex]],
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Add all products from requisition list to the shopping cart.
     *
     * @param array $requisitionList
     * @return void
     */
    private function addProductsToCartFromList(array $requisitionList)
    {
        $this->requisitionListGrid->open();
        $this->requisitionListGrid->getRequisitionListGrid()->openRequisitionListByName($requisitionList['name']);
        $this->requisitionListView->getRequisitionListContent()->selectProducts();
        $this->requisitionListView->getRequisitionListContent()->addProductsToCart();
    }

    /**
     * Update product using dataset.
     *
     * @param CatalogProductSimple|ConfigurableProduct $product
     * @param string $dataset
     * @return void
     */
    private function updateProduct($product, $dataset)
    {
        $filter = ['sku' => $product->getSku()];
        $data = $this->fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => $dataset,
            ]
        );
        $this->productIndex->open();
        $this->productIndex->getProductGrid()->searchAndOpen($filter);
        $this->productEdit->getProductForm()->fill($data);
        $this->productEdit->getFormPageActions()->save();
    }
}
