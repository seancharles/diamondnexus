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
 * 2. Create simple product.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Assert that "Add to Requisition List" link is visible in customer menu.
 * 3. Go to the product page.
 * 4. Assert that "Add to Requisition List" link is visible on product page.
 * 5. Go to the category page.
 * 6. Assert that "Add to Requisition List" link is visible on category page.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68152
 */
class EnableRequisitionListTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Enable requisition list.
     *
     * @param Customer $customer
     * @param CatalogProductSimple $product
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, CatalogProductSimple $product, $configData = null)
    {
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
        $product->persist();
        $category = $product->hasData('category_ids') ?
            $product->getDataFieldConfig('category_ids')['source']->getCategories()[0] : null;
        $this->loginCustomer($customer);

        return [
            'product' => $product,
            'category' => $category
        ];
    }
}
