<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Rma\Test\Page\Adminhtml\RmaIndex;
use Magento\Rma\Test\Page\Adminhtml\RmaNew;
use Magento\Rma\Test\Page\Adminhtml\RmaChooseOrder;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Rma\Test\Fixture\Rma;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Create company with customer.
 * 2. Create order with Payment on Account method.
 *
 * Steps:
 * 1. Submit shipment for the order.
 * 2. Create a return for the order.
 * 3. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68425
 */
class PaymentOnAccountReturnTest extends Injectable
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Object Manager.
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Rma index page on backend.
     *
     * @var RmaIndex
     */
    private $rmaIndex;

    /**
     * Rma choose order page on backend.
     *
     * @var RmaChooseOrder
     */
    private $rmaChooseOrder;

    /**
     * Rma choose order page on backend.
     *
     * @var RmaNew
     */
    private $rmaNew;

    /**
     * Inject data.
     *
     * @param FixtureFactory $fixtureFactory
     * @param RmaIndex $rmaIndex
     * @param RmaChooseOrder $rmaChooseOrder
     * @param RmaNew $rmaNew
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        RmaIndex $rmaIndex,
        RmaChooseOrder $rmaChooseOrder,
        RmaNew $rmaNew
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->rmaIndex = $rmaIndex;
        $this->rmaChooseOrder = $rmaChooseOrder;
        $this->rmaNew = $rmaNew;
    }

    /**
     * Test return functionality for Payment on Account method.
     *
     * @param string $companyDataset
     * @param Customer $customer
     * @param string $orderInjectable
     * @param Rma $rma
     * @param bool $createReturn
     * @param string|null $configData
     * @return array
     */
    public function test(
        $companyDataset,
        Customer $customer,
        $orderInjectable,
        Rma $rma,
        $createReturn = false,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customer->persist();
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => $companyDataset,
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

        //Steps:
        $createShipmentStep = $this->objectManager->create(
            \Magento\Sales\Test\TestStep\CreateShipmentStep::class,
            ['order' => $order]
        );
        $createShipmentStep->run();

        $rmaData = $rma->getData();
        $store = $order->getDataFieldConfig('store_id')['source']->getStore();
        $data = $order->getData();
        $data['store_id'] = ['data' => $store->getData()];
        $data['entity_id'] = ['value' => $order->getEntityId()];
        $data['customer_id'] = ['customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()];
        $rmaData['order_id'] = ['data' => $data];
        $rma = $this->fixtureFactory->createByCode('rma', ['data' => $rmaData]);

        if ($createReturn) {
            $this->rmaIndex->open();
            $this->rmaIndex->getGridPageActions()->addNew();
            $this->rmaChooseOrder->getOrderGrid()->searchAndOpen(['id' => $order->getId()]);
            $this->rmaNew->getRmaForm()->fill($rma);
            $this->rmaNew->getPageActions()->save();
        }

        return [
            'companies' => [$company],
            'orderId' => $order->getId(),
            'rma' => $rma,
        ];
    }
}
