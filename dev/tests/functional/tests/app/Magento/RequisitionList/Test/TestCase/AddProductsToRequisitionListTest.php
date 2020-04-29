<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create simple products.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Create new requisition list.
 * 3. Add products to the requisition list.
 * 4. Perform assertions.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68139, @ZephyrId MAGETWO-68197, @ZephyrId MAGETWO-68187, @ZephyrId MAGETWO-68210
 */
class AddProductsToRequisitionListTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Add products to requisition list.
     *
     * @param Customer $customer
     * @param array $productsList
     * @param array $requisitionList
     * @param string $configData
     * @param array $updateData
     * @param string $productToUpdate
     * @param string $taxRule
     * @return array
     */
    public function test(
        Customer $customer,
        array $productsList,
        $requisitionList,
        $configData = null,
        array $updateData = [],
        $productToUpdate = '',
        $taxRule = null
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
        $this->taxRule = $taxRule;
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
        $products = $this->createProducts($productsList);
        $this->loginCustomer($customer);
        $requisitionList = $this->createRequisitionList($requisitionList);
        $this->addToRequisitionList($products, $requisitionList);
        if (!empty($productToUpdate)) {
            $this->requisitionListGrid->open();
            $this->requisitionListGrid->getRequisitionListGrid()->openFirstItem();
            $this->requisitionListView->getRequisitionListContent()->clickEditButton($productToUpdate);
            $this->requisitionListItemConfigure->getProductBlock()->updateRequisitionListItem($updateData);
        }

        return [
            'products' => $products,
            'name' => $requisitionList['name'],
            'productToUpdate' => $productToUpdate,
            'updateData' => $updateData
        ];
    }
}
