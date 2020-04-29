<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Company\Test\Fixture\CompanyAttributes;

/**
 * Preconditions:
 * 1. Create a company admin.
 * 2. Submit a quote as a company admin (admin quote).
 * 3. Place an order as company admin (admin order).
 * 4. Create a child user of company admin.
 * 5. Submit a quote as a child user (child quote).
 * 6. Place an order as a child user (child order).
 *
 * Steps:
 * 1. Login to storefront as a company admin.
 * 2. Go to My Account -> My Quotes.
 * 3. Open subordinate's Quote by "View" button.
 * 4. Go back to Quotes grid and click "Show My Quotes" link.
 * 5. Go to My Account -> My Orders.
 * 6. Open subordinate's order by clicking on "View" button.
 * 7. Go back to Orders grid and click "Show My Orders" link.
 * 8. Perform all assertions.
 *
 * @group NegotiableQuote
 * @ZephyrId MAGETWO-68235, @ZephyrId MAGETWO-67880
 *
 */
class ViewSubordinateContentsTest extends AbstractQuoteNegotiationTest
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'acceptance_test';
    /* end tags */

    /**
     * Company admin data
     *
     * @var \Magento\Customer\Test\Fixture\Customer
     */
    private $companyAdmin;

    /**
     * Sub user data
     *
     * @var \Magento\Customer\Test\Fixture\Customer
     */
    private $subUser;

    /**
     * Company admin order id
     *
     * @var string
     */
    private $adminOrderId;

    /**
     * Company subuser order id
     *
     * @var string
     */
    private $subUserOrderId;

    /**
     * Test quote negotiation
     *
     * @param array $productsList
     * @param Customer $companyAdmin
     * @param Customer $userWithoutCompany
     * @param array $quote
     * @param array $messages
     * @param array $steps
     * @param array $shipping
     * @param array $payment
     * @param string $configData
     * @return array
     */
    public function test(
        array $productsList,
        Customer $companyAdmin,
        Customer $userWithoutCompany,
        array $quote = [],
        array $messages = [],
        array $steps = [],
        array $shipping = [],
        array $payment = [],
        $configData = null
    ) {
        $this->configData = $configData;
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData]
        )->run();
        $this->cleanCache();

        // Preconditions
        $companyAdmin->persist();
        $this->subUser = $userWithoutCompany;
        $this->subUser->persist();
        $this->companyAdmin = $companyAdmin;
        $company = $this->fixtureFactory->createByCode(
            'company',
            [
                'dataset' => 'company_with_required_fields_and_sales_rep',
                'data' => [
                    'email' => $this->companyAdmin->getEmail(),
                ],
            ]
        );
        $company->persist();
        $subUser = $this->fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'company_customer_job_phone',
                'data' => [
                    'email' => $this->subUser->getEmail(),
                ],
            ]
        );
        /** @var CompanyAttributes $subUserAttributes */
        $subUserAttributes = $this->fixtureFactory->createByCode(
            'company_attributes',
            [
                'data' => [
                    'customer_id' => $this->subUser->getId(),
                    'company_id' => $company->getId(),
                    'job_title' => $subUser->getJobTitle(),
                    'telephone' => $subUser->getTelephone(),
                    'status' => 1
                ]
            ]
        );
        $subUserAttributes->persist();

        $this->loginCustomer($this->companyAdmin);
        $this->companyPage->open();
        $this->shipping = $shipping;
        $products = $this->createProducts($productsList);
        $this->products = $products;
        $this->payment = $payment;

        //%isolation% not working on arrays
        if (isset($quote['quote-name'])) {
            $quote['quote-name'] .= time();
            $this->quote = $quote;
            $this->messages = array_merge($messages, [$quote['quote-message']]);
        }

        $this->addToCart($products);
        $this->requestQuote($quote);
        $adminQuote = $quote;
        foreach ($steps as $step) {
            $classMethod = $this->getMethodName($step);
            $result = $this->$classMethod();
            foreach ($result as $key => $value) {
                $$key = $value;
            }
        }
        $this->frontPerformSubUserActions();

        return [
            'adminQuote' => $adminQuote,
            'subUserQuote' => $this->quote,
            'admin' => $this->companyAdmin,
            'subUser' => $this->subUser,
            'adminOrderId' => $this->adminOrderId,
            'subUserOrderId' => $this->subUserOrderId
        ];
    }

    /**
     * Request a quote and place order
     *
     * @return array
     */
    protected function frontPerformSubUserActions()
    {
        $this->frontRelogin($this->subUser);
        $this->quote['quote-name'] .= time();
        $this->addToCart($this->products);
        $this->requestQuote($this->quote);
        $this->adminSend();
        $this->adminOrderId = $this->updateData['orderId'];
        $this->frontPlaceOrder();
        $this->subUserOrderId = $this->updateData['orderId'];
        $this->frontRelogin($this->companyAdmin);

        return [];
    }

    /**
     * Login as user
     *
     * @param Customer $user
     * @return array
     */
    protected function frontRelogin(Customer $user)
    {
        $this->logoutCustomerOnFrontendStep->run();
        $this->loginCustomer($user);

        return [];
    }
}
