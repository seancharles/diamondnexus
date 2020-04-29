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
 * 3. Set maximum allowed number of requisition lists for customer.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Create maximum allowed number of requisition lists.
 * 3. Add products to the created requisition lists.
 * 4. Assert that "Add to Requisition List" link is not visible in grid.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68153
 */
class RequisitionListsLimitTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Create max allowed number of requisition lists.
     *
     * @param Customer $customer
     * @param array $productsList
     * @param array $requisitionList
     * @param int $requisitionListsNumber
     * @param string $configData
     * @return void
     */
    public function test(
        Customer $customer,
        array $productsList,
        $requisitionList,
        $requisitionListsNumber,
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
        $this->loginCustomer($customer);

        for ($i = 1; $i <=  $requisitionListsNumber; $i++) {
            $requisitionList = $this->createRequisitionList($requisitionList);
            $this->addToRequisitionList($products, $requisitionList);
        }
        $this->requisitionListGrid->open();
    }
}
