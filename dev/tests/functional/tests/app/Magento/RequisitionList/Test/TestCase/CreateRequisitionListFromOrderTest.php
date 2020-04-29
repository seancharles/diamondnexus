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
 * 2. Create order.
 *
 * Steps:
 * 1. Login as a customer on the Storefront.
 * 2. Go to order history page.
 * 3. Open newly created order.
 * 4. Click "Add to Requisition List" button.
 * 5. Fill in the form.
 * 6. Save the requisition list.
 * 7. Perform assertions.
 *
 * @group RequisitionList
 * @ZephyrId MAGETWO-68195
 */
class CreateRequisitionListFromOrderTest extends AbstractRequisitionListTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Create requisition list from order.
     *
     * @param Customer $customer
     * @param array $requisitionList
     * @param string $configData
     * @return array
     */
    public function test(Customer $customer, $requisitionList, $configData = null)
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
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => 'default',
                'data' => ['customer_id' => ['customer' => $customer]]
            ]
        );
        $order->persist();
        $products = $order->getEntityId()['products'];
        $this->loginCustomer($customer);
        $this->createRequisitionListFromOrder($requisitionList, $order->getId());

        return [
            'products' => $products,
            'name' => $requisitionList['name']
        ];
    }
}
