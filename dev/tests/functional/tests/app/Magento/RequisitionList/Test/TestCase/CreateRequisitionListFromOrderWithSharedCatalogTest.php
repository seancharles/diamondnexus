<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestCase;

use Magento\SharedCatalog\Test\Fixture\SharedCatalog;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Preconditions:
 * 1. Create customer.
 * 2. Create company and assign customer to it as company admin
 * 3. Create custom shared catalog
 * 4. Assign company to this shared catalog
 * 5. Create order.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Go to order history page.
 * 3. Open newly created order.
 * 4. Click "Add to Requisition List" button.
 * 5. Fill in the form.
 * 6. Save the requisition list.
 * 7. Perform assertions.
 * 8. Unassign ordered product from shared catalog
 * 9. Try to create requisition list from view order page again
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68195
 */
class CreateRequisitionListFromOrderWithSharedCatalogTest extends AbstractRequisitionListTest
{
    /**
     * Create requisition list from order.
     *
     * @param SharedCatalog $sharedCatalog
     * @param CatalogProductSimple $product
     * @param array $requisitionList
     * @param bool $unassignFromSharedCatalog
     * @param string $configData
     * @return array
     */

    public function test(
        SharedCatalog $sharedCatalog,
        CatalogProductSimple $product,
        $requisitionList,
        $unassignFromSharedCatalog = false,
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $product->persist();
        $sharedCatalog->persist();
        $this->testStepFactory->create(
            \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
            ['sharedCatalog' => $sharedCatalog, 'products' => [$product]]
        )->run();
        $customer = $sharedCatalog->getDataFieldConfig('companies')['source']->getCompanies()[0]
            ->getDataFieldConfig('customer')['source']->getCustomer();
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => 'default',
                'data' => ['customer_id' => ['customer' => $customer], 'entity_id' => ['products' => [$product]]]
            ]
        );
        $order->persist();
        if ($unassignFromSharedCatalog) {
            $this->testStepFactory->create(
                \Magento\SharedCatalog\Test\TestStep\ConfigureSharedCatalogStep::class,
                ['sharedCatalog' => $sharedCatalog, 'products' => [],'unassignProducts' => [$product]]
            )->run();
        }
        $this->loginCustomer($customer);
        $this->createRequisitionListFromOrder($requisitionList, $order->getId());

        return [
            'products' => [$product],
            'name' => $requisitionList['name']
        ];
    }

    /**
     * Create requisition list from order.
     *
     * @param array $requisitionList
     * @param int $orderId
     * @return void
     */
    public function createRequisitionListFromOrder(array $requisitionList, $orderId)
    {
        $this->orderHistory->open();
        $this->orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        $this->orderView->getRequisitionListActions()->clickCreateButton();
        $this->requisitionListGrid->getRequisitionListPopup()->fillForm($requisitionList);
        $this->requisitionListGrid->getRequisitionListPopup()->confirm();
    }

    /**
     * Logout customer from Storefront account and roll back config settings
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
