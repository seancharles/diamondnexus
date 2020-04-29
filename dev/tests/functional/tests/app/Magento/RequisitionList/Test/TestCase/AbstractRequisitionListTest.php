<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequisitionList\Test\TestCase;

use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\ObjectManager;
use Magento\Customer\Test\Fixture\Customer;
use Magento\RequisitionList\Test\Page\RequisitionListGrid;
use Magento\RequisitionList\Test\Page\RequisitionListView;
use Magento\RequisitionList\Test\Page\RequisitionListItemConfigure;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestStep\TestStepFactory;
use Magento\Sales\Test\Page\OrderHistory;
use Magento\Sales\Test\Page\CustomerOrderView;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;

/**
 * Abstract class for requisition list functional tests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractRequisitionListTest extends Injectable
{
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
     * Requisition list grid.
     *
     * @var RequisitionListGrid
     */
    protected $requisitionListGrid;

    /**
     * Requisition list detail page.
     *
     * @var RequisitionListView
     */
    protected $requisitionListView;

    /**
     * Requisition list item configure page.
     *
     * @var RequisitionListItemConfigure
     */
    protected $requisitionListItemConfigure;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Test step factory.
     *
     * @var TestStepFactory
     */
    protected $testStepFactory;

    /**
     * Order history page.
     *
     * @var OrderHistory
     */
    protected $orderHistory;

    /**
     * Order view page.
     *
     * @var CustomerOrderView
     */
    protected $orderView;

    /**
     * Tax rule.
     *
     * @var TaxRule
     */
    protected $taxRule;

    /**
     * Product page with a grid.
     *
     * @var CatalogProductIndex
     */
    protected $productIndex;

    /**
     * Page to update a product.
     *
     * @var CatalogProductEdit
     */
    protected $productEdit;

    /**
     * Inject pages.
     *
     * @param ObjectManager $objectManager
     * @param RequisitionListGrid $requisitionListGrid
     * @param RequisitionListView $requisitionListView
     * @param RequisitionListItemConfigure $requisitionListItemConfigure
     * @param FixtureFactory $fixtureFactory
     * @param TestStepFactory $testStepFactory
     * @param OrderHistory $orderHistory
     * @param CustomerOrderView $orderView
     * @param CatalogProductIndex $productIndex
     * @param CatalogProductEdit $productEdit
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __inject(
        ObjectManager $objectManager,
        RequisitionListGrid $requisitionListGrid,
        RequisitionListView $requisitionListView,
        RequisitionListItemConfigure $requisitionListItemConfigure,
        FixtureFactory $fixtureFactory,
        TestStepFactory $testStepFactory,
        OrderHistory $orderHistory,
        CustomerOrderView $orderView,
        CatalogProductIndex $productIndex,
        CatalogProductEdit $productEdit
    ) {
        $this->objectManager = $objectManager;
        $this->requisitionListGrid = $requisitionListGrid;
        $this->requisitionListView = $requisitionListView;
        $this->requisitionListItemConfigure = $requisitionListItemConfigure;
        $this->fixtureFactory = $fixtureFactory;
        $this->testStepFactory = $testStepFactory;
        $this->orderHistory = $orderHistory;
        $this->orderView = $orderView;
        $this->productIndex = $productIndex;
        $this->productEdit = $productEdit;
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    protected function createProducts($products)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $loginCustomerOnFrontendStep = $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        );
        $loginCustomerOnFrontendStep->run();
    }

    /**
     * Create new requisition list.
     *
     * @param array $requisitionList
     * @return array
     */
    protected function createRequisitionList(array $requisitionList)
    {
        $requisitionList['name'] .= time();
        $this->requisitionListGrid->open();
        $this->requisitionListGrid->getRequisitionListActions()->clickCreateButton();
        $this->requisitionListGrid->getRequisitionListPopup()->fillForm($requisitionList);
        $this->requisitionListGrid->getRequisitionListPopup()->confirm();
        $this->requisitionListGrid->open();

        return $requisitionList;
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
        $requisitionList['name'] .= time();
        $this->orderHistory->open();
        $this->orderHistory->getOrderHistoryBlock()->openOrderById($orderId);
        $this->orderView->getRequisitionListActions()->clickCreateButton();
        $this->requisitionListGrid->getRequisitionListPopup()->fillForm($requisitionList);
        $this->requisitionListGrid->getRequisitionListPopup()->confirm();
        $this->orderView->getRequisitionListMessages()->waitForSuccessMessage();
    }

    /**
     * Add products to a requisition list.
     *
     * @param array $products
     * @param array $requisitionList
     * @return void
     */
    protected function addToRequisitionList(array $products, $requisitionList)
    {
        $this->objectManager->create(
            \Magento\RequisitionList\Test\TestStep\AddProductsToRequisitionListStep::class,
            [
                'products' => $products,
                'requisitionListName' => $requisitionList['name']
            ]
        )->run();
    }

    /**
     * Logout the customer from Storefront account and reset configuration settings to default.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
        if ($this->taxRule) {
            $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class, [])->run();
        }
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
