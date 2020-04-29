<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CompanyCredit\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Preconditions:
 * 1. Create 2 companies with 2 users in the first company and 1 in the second one.
 * 2. Create order with a user from the first company.
 *
 * Steps:
 * 1. Reassign customer who placed order to the second company.
 * 2. Cancel the order.
 * 3. Perform all assertions.
 *
 * @group CompanyCredit
 * @ZephyrId MAGETWO-68426
 */
class RevertForReassignedCustomerTest extends AbstractCompanyCreditTest
{
    /* tags */
    const MVP       = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var \Magento\Mtf\Fixture\FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Customer edit page.
     *
     * @var \Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit
     */
    private $customerEdit;

    /**
     * Order index page.
     *
     * @var \Magento\Sales\Test\Page\Adminhtml\OrderIndex
     */
    private $orderIndex;

    /**
     * Order view page.
     *
     * @var \Magento\Sales\Test\Page\Adminhtml\SalesOrderView
     */
    private $salesOrderView;

    /**
     * New order memo page.
     *
     * @var \Magento\Company\Test\Page\Company
     */
    private $companyPage;

    /**
     * Inject dependencies.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerIndexEdit $customerEdit
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param CompanyPage $companyPage
     * @return void
     */
    public function __inject(
        FixtureFactory $fixtureFactory,
        CustomerIndexEdit $customerEdit,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        CompanyPage $companyPage
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->customerEdit = $customerEdit;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->companyPage = $companyPage;
    }

    /**
     * Test revert functionality for Payment on Account method.
     *
     * @param string $companyDataset
     * @param string $customerDataset
     * @param string $orderInjectable
     * @param string|null $configData
     * @return array
     */
    public function test(
        $companyDataset,
        $customerDataset,
        $orderInjectable,
        $configData = null
    ) {
        //Preconditions:
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $customers = [];
        $companies = [];
        for ($i = 0; $i < 2; $i++) {
            $customers[$i] = $this->fixtureFactory->createByCode(
                'customer',
                [
                    'dataset' => $customerDataset,
                ]
            );
            $customers[$i]->persist();
            $companies[$i] = $this->fixtureFactory->createByCode(
                'company',
                [
                    'dataset' => $companyDataset,
                    'data' => [
                        'email' => $customers[$i]->getEmail(),
                    ],
                ]
            );
            $companies[$i]->persist();
        }
        $companyUser = $this->createCompanyUser($customers[0]);
        $order = $this->fixtureFactory->createByCode(
            'orderInjectable',
            [
                'dataset' => $orderInjectable,
                'data' => ['customer_id' => ['customer' => $companyUser]]
            ]
        );
        $order->persist();

        //Steps:
        $this->changeCustomerCompany($companyUser, $companies[1]);
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $order->getId()]);
        $this->salesOrderView->getPageActions()->cancel();

        return [
            'companies' => $companies,
            'orderId' => $order->getId(),
        ];
    }

    /**
     * Create a new company user.
     *
     * @param FixtureInterface $companyAdmin
     * @return FixtureInterface
     */
    private function createCompanyUser(FixtureInterface $companyAdmin)
    {
        $companyUser = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_3',
            ]
        );
        $companyUser->persist();
        $customer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_1',
                'data' => [
                    'firstname' => $companyUser->getFirstname(),
                    'lastname' => $companyUser->getLastName(),
                    'email' => $companyUser->getEmail(),
                ],
            ]
        );
        $this->loginCustomer($companyAdmin);
        $this->companyPage->open();
        $this->companyPage->getTreeControl()->clickAddCustomer();
        $this->companyPage->getCustomerPopup()->fill($customer);
        $this->companyPage->getCustomerPopup()->submit();

        return $companyUser;
    }

    /**
     * Change company for the customer.
     *
     * @param FixtureInterface $companyUser
     * @param FixtureInterface $company
     * @return void
     */
    private function changeCustomerCompany(FixtureInterface $companyUser, FixtureInterface $company)
    {
        $this->customerEdit->open(['id' => $companyUser->getId()]);
        $customer = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'default_with_company_association',
                'data' => [
                    'company_association' => $company->getCompanyName(),
                ],
            ]
        );
        $this->customerEdit->getCustomerForm()->updateCustomer($customer);
        $this->customerEdit->getCompanyModalBlock()->acceptAlert();
        $this->customerEdit->getPageActionsBlock()->save();
    }

    /**
     * Logout customer.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
        parent::tearDown();
    }
}
