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
 * 2. Create order.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Create requisition list from order.
 * 3. Move/copy products from one requisition list to another.
 * 4. Perform assertions.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68207
 */
class MoveCopyRequisitionListTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Performs action on requisition list.
     *
     * @param string $action
     * @param array $requisitionList
     * @return void
     */
    protected function performAction($action, array $requisitionList)
    {
        $this->requisitionListView->getRequisitionListContent()->selectProducts();
        $this->requisitionListView->getRequisitionListContent()->performAction(ucfirst($action));
        $this->requisitionListGrid->getRequisitionListPopup()->fillForm($requisitionList);
        $this->requisitionListGrid->getRequisitionListPopup()->confirm();
        $this->requisitionListView->getRequisitionListMessages()->waitForSuccessMessage();
    }

    /**
     * Create max allowed number of requisition lists.
     *
     * @param Customer $customer
     * @param string $orderInjectable
     * @param array $requisitionList
     * @param string $action
     * @param string $secondList
     * @param string $configData [optional]
     * @return array
     */
    public function test(
        Customer $customer,
        $orderInjectable,
        array $requisitionList,
        $action,
        $secondList,
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
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => $orderInjectable,
                'data' => ['customer_id' => ['customer' => $customer]]
            ]
        );
        $order->persist();
        $products = $order->getEntityId()['products'];
        $this->loginCustomer($customer);
        $this->createRequisitionListFromOrder($requisitionList, $order->getId());
        $this->requisitionListGrid->open();
        $this->requisitionListGrid->getRequisitionListGrid()->openFirstItem();
        $this->performAction($action, $secondList);

        return [
            'products' => $products,
            'name' => $secondList['name']
        ];
    }
}
