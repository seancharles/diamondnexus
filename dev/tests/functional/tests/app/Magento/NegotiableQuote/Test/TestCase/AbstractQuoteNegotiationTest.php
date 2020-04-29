<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\NegotiableQuote\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\TestCase\Injectable;
use Magento\NegotiableQuote\Test\Page\NegotiableCheckoutCart;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteIndex;
use Magento\NegotiableQuote\Test\Page\Adminhtml\NegotiableQuoteEdit;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteGrid;
use Magento\NegotiableQuote\Test\Page\NegotiableQuoteView;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Customer\Test\Fixture\Address;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Tax\Test\Fixture\TaxRule;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceNew;
use Magento\Sales\Test\Page\Adminhtml\OrderInvoiceView;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\PageCache\Test\Page\Adminhtml\AdminCache;
use Magento\Company\Test\Page\Company as CompanyPage;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;

/**
 * Abstract negotiable quote test
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractQuoteNegotiationTest extends Injectable
{
    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Cart page
     *
     * @var NegotiableCheckoutCart
     */
    protected $cartPage;

    /**
     * Negotiable quote grid in admin
     *
     * @var NegotiableQuoteIndex
     */
    protected $negotiableQuoteGrid;

    /**
     * Negotiable quote edit page in admin
     *
     * @var NegotiableQuoteEdit
     */
    protected $negotiableQuoteView;

    /**
     * Customer log out step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontendStep;

    /**
     * Quote Storefront grid
     *
     * @var NegotiableQuoteGrid
     */
    protected $quoteFrontendGrid;

    /**
     * Quote Storefront view
     *
     * @var NegotiableQuoteView
     */
    protected $quoteFrontendView;

    /**
     * Update data
     *
     * @var array
     */
    protected $updateData;

    /**
     * Quote
     *
     * @var array
     */
    protected $quote;

    /**
     * CustomerAddressEdit page
     *
     * @var CustomerAddressEdit
     */
    protected $customerAddressEdit;

    /**
     * CheckoutOnepage page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Orders Page.
     *
     * @var OrderIndex
     */
    protected $orderIndex;

    /**
     * Order View Page.
     *
     * @var SalesOrderView
     */
    protected $salesOrderView;

    /**
     * Order New Invoice Page.
     *
     * @var OrderInvoiceNew
     */
    protected $orderInvoiceNew;

    /**
     * Order invoice view page.
     *
     * @var OrderInvoiceView
     */
    protected $orderInvoiceView;

    /**
     * CatalogProductIndex
     *
     * @var CatalogProductIndex
     */
    protected $catalogProductIndex;

    /**
     * Quote messages
     *
     * @var array
     */
    protected $messages;

    /**
     * Fixture factory instance.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Shipping address
     *
     * @var Address
     */
    protected $address;

    /**
     * Tax rule
     *
     * @var TaxRule
     */
    protected $taxRule;

    /**
     * Tax value
     *
     * @var int
     */
    protected $tax;

    /**
     * @var \Magento\GiftCardAccount\Test\Fixture\GiftCardAccount
     */
    protected $giftCard;

    /**
     * @var \Magento\SalesRule\Test\Fixture\SalesRule
     */
    protected $salesRule;

    /**
     * @var array
     */
    protected $additionalQuote;

    /**
     * @var array
     */
    protected $products = [];

    /**
     * Configuration setting.
     *
     * @var string
     */
    protected $configData;

    /**
     * Shipping
     *
     * @var array
     */
    protected $shipping;

    /**
     * Coupon
     *
     * @var \Magento\SalesRule\Test\Fixture\SalesRule
     */
    protected $coupon;

    /**
     * Customer
     *
     * @var \Magento\Customer\Test\Fixture\Customer
     */
    protected $customer;

    /**
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * @var CatalogProductEdit
     */
    protected $catalogProductEdit;

    /**
     * SKUs array
     *
     * @var array
     */
    protected $skuArray;

    /**
     * Qty
     *
     * @var int
     */
    protected $qty;

    /**
     * Page AdminCache.
     *
     * @var AdminCache
     */
    protected $adminCache;

    /**
     * Company page
     *
     * @var CompanyPage $companyPage
     */
    protected $companyPage;

    /**
     * Grid spinner selector.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * @var array
     */
    protected $payment;

    /**
     * Perform needed injections
     *
     * @param ObjectManager $objectManager
     * @param CheckoutCart $cartPage
     * @param NegotiableQuoteIndex $negotiableQuoteGrid
     * @param NegotiableQuoteEdit $negotiableQuoteView
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep
     * @param NegotiableQuoteGrid $quoteFrontendGrid
     * @param NegotiableQuoteView $quoteFrontendView
     * @param CustomerAddressEdit $customerAddressEdit
     * @param CheckoutOnepage $checkoutOnepage
     * @param FixtureFactory $fixtureFactory
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param OrderInvoiceNew $orderInvoiceNew
     * @param OrderInvoiceView $orderInvoiceView
     * @param CustomerIndexEdit $customerIndexEdit
     * @param AdminCache $adminCache
     * @param CompanyPage $companyPage
     * @param CatalogProductIndex $catalogProductIndex
     * @param CatalogProductEdit $catalogProductEdit
     */
    public function __inject(
        ObjectManager $objectManager,
        NegotiableCheckoutCart $cartPage,
        NegotiableQuoteIndex $negotiableQuoteGrid,
        NegotiableQuoteEdit $negotiableQuoteView,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontendStep,
        NegotiableQuoteGrid $quoteFrontendGrid,
        NegotiableQuoteView $quoteFrontendView,
        CustomerAddressEdit $customerAddressEdit,
        CheckoutOnepage $checkoutOnepage,
        FixtureFactory $fixtureFactory,
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        OrderInvoiceNew $orderInvoiceNew,
        OrderInvoiceView $orderInvoiceView,
        CustomerIndexEdit $customerIndexEdit,
        AdminCache $adminCache,
        CompanyPage $companyPage,
        CatalogProductIndex $catalogProductIndex,
        CatalogProductEdit $catalogProductEdit
    ) {
        $this->objectManager = $objectManager;
        $this->cartPage = $cartPage;
        $this->negotiableQuoteGrid = $negotiableQuoteGrid;
        $this->negotiableQuoteView = $negotiableQuoteView;
        $this->logoutCustomerOnFrontendStep = $logoutCustomerOnFrontendStep;
        $this->quoteFrontendGrid = $quoteFrontendGrid;
        $this->quoteFrontendView = $quoteFrontendView;
        $this->customerAddressEdit = $customerAddressEdit;
        $this->checkoutOnepage = $checkoutOnepage;
        $this->fixtureFactory = $fixtureFactory;
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->orderInvoiceNew = $orderInvoiceNew;
        $this->orderInvoiceView = $orderInvoiceView;
        $this->customerIndexEdit = $customerIndexEdit;
        $this->adminCache = $adminCache;
        $this->companyPage = $companyPage;
        $this->catalogProductIndex = $catalogProductIndex;
        $this->catalogProductEdit = $catalogProductEdit;
    }

    /**
     * Add address
     *
     * @param Address $address
     */
    protected function addAddress(Address $address)
    {
        $this->quoteFrontendView->getQuoteDetails()->clickEditAddress();
        $this->customerAddressEdit->getEditForm()->editCustomerAddress($address);
    }

    /**
     * Request a quote
     *
     * @param array $quote
     * @return array
     */
    protected function requestQuote(array $quote)
    {
        $this->cartPage->open();
        $this->cartPage->getRequestQuote()->requestQuote();
        $this->cartPage->getRequestQuotePopup()->fillForm($quote);
        $this->cartPage->getRequestQuotePopup()->submitQuote();
        $this->quoteFrontendGrid->getQuoteGrid()->waitForElementVisible($this->spinner);
        $this->quoteFrontendGrid->getQuoteGrid()->waitForElementNotVisible($this->spinner);
        return $this->updateData['historyLog'][] = 'Comment';
    }

    /**
     * Add products to cart
     *
     * @param array $products
     * @return void
     */
    protected function addToCart(array $products)
    {
        $addToCartStep = ObjectManager::getInstance()->create(
            \Magento\Checkout\Test\TestStep\AddProductsToTheCartStep::class,
            ['products' => $products]
        );
        $addToCartStep->run();
    }

    /**
     * Login customer.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomer(Customer $customer)
    {
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep::class,
            ['customer' => $customer]
        )->run();
    }

    /**
     * Create products.
     *
     * @param array $products
     * @return array
     */
    protected function createProducts(array $products)
    {
        $createProductsStep = $this->objectManager->create(
            \Magento\Catalog\Test\TestStep\CreateProductsStep::class,
            ['products' => $products]
        );

        return $createProductsStep->run()['products'];
    }

    /**
     * Gets method name
     *
     * @param string $step
     * @return string
     */
    protected function getMethodName($step)
    {
        $step = explode('_', $step);
        $method = '';
        foreach ($step as $value) {
            if ($value) {
                $value = ucfirst($value);
            }
            $method .= $value;
        }

        return $method;
    }

    /**
     * Decline quote in admin
     *
     * @return array
     * @throws \Exception
     */
    protected function adminDecline()
    {
        $this->negotiableQuoteGrid->open();
        $quoteName = isset($this->additionalQuote['quote-name'])
            ? $this->additionalQuote['quote-name']
            : $this->quote['quote-name'];
        $filter = ['quote_name' => $quoteName];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetailsActions()->decline();
        $this->negotiableQuoteView->getQuoteDeclinePopup()
            ->fillDeclineReason($this->messages['decline-comment'])
            ->confirmDecline();
        $this->updateData['historyLog'][] = 'Comment';
        $this->updateData['historyLog'][] = 'Expiration Date';
        $this->updateData['historyLog'][] = 'Status';

        return [
            'frontStatus' => 'DECLINED',
            'frontLock' => false,
            'disabledButtonsFront' => [],
            'adminStatus' => 'Declined',
            'adminLock' => true,
            'disabledButtonsAdmin' => ['saveAsDraft', 'decline', 'send']
        ];
    }

    /**
     * Create invoice
     */
    protected function createInvoice()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()->searchAndOpen(['id' => $this->updateData['orderId']]);
        $this->salesOrderView->getPageActions()->invoice();
        $this->orderInvoiceNew->getFormBlock()->submit();
        $this->salesOrderView->getOrderForm()->openTab('invoices');
        $invoiceIds = $this->salesOrderView->getOrderForm()->getTab('invoices')->getGridBlock()->getIds();

        return [
            'invoiceIds' => $invoiceIds
        ];
    }

    /**
     * Save quote as draft
     *
     * @return array
     */
    protected function saveQuoteAsDraft()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        if (isset($this->updateData['discountType'])) {
            $this->negotiableQuoteView
                ->getQuoteDetails()
                ->fillDiscount($this->updateData['discountType'], $this->updateData['discountValue']);
        }
        $this->negotiableQuoteView->getQuoteDetailsActions()->saveAsDraft();

        $this->updateData['disabledButtonsFront'] = ['checkout', 'send', 'delete'];
        $this->updateData['disabledButtonsAdmin'] = [];
        $this->updateData['frontStatus'] = 'PENDING';
        $this->updateData['adminStatus'] = 'Open';
        $this->updateData['frontLock'] = true;
        $this->updateData['adminLock'] = false;
        $this->updateData['frontDiscountApplied'] = false;
        return $this->updateData;
    }

    /**
     * Place order on Storefront (customer has no addresses)
     *
     * @return array
     */
    protected function frontPlaceOrderWithoutAddress()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->checkout();
        $address = $this->fixtureFactory->createByCode(
            'address',
            [
                'dataset' => 'US_address_1_without_email',
            ]
        );
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingAddressStep::class,
            [
                'shippingAddress' => $address
            ]
        )->run();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            [
                'shipping' => $this->shipping
            ]
        )->run();
        if (!empty($this->payment)) {
            $this->objectManager->create(
                \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
                ['payment' => $this->payment]
            )->run();
        }
        $this->updateData['orderId'] = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return $this->updateData;
    }

    /**
     * Select default shipping address in a popup
     *
     * @return array
     */
    protected function selectDefaultShippingAddress()
    {
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->clickEditAddress();
        $this->quoteFrontendView->getEditAddress()->save();
        return [];
    }

    /**
     * Place order on Storefront
     *
     * @return array
     */
    protected function frontPlaceOrder()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->checkout();
        $this->objectManager->create(
            \Magento\Checkout\Test\TestStep\FillShippingMethodStep::class,
            [
                'shipping' => $this->shipping
            ]
        )->run();
        if (!empty($this->payment)) {
            $this->objectManager->create(
                \Magento\Checkout\Test\TestStep\SelectPaymentMethodStep::class,
                ['payment' => $this->payment]
            )->run();
        }
        $this->updateData['orderId'] = $this->objectManager
            ->create(
                \Magento\Checkout\Test\TestStep\PlaceOrderStep::class,
                []
            )
            ->run()['orderId'];

        return $this->updateData;
    }

    /**
     * Update quote in admin
     *
     * @return array
     */
    protected function adminSend()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
        $this->updateData['historyLog'][] = 'Expiration Date';
        $this->updateData['historyLog'][] = 'Status';
        return [
            'frontLock' => false
        ];
    }

    /**
     * Delete quote on Storefront
     *
     * @return array
     */
    protected function frontDelete()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->delete();
        return [];
    }

    /**
     * Open edit quote page in admin
     *
     * @param array $quote
     * @throws \Exception
     */
    protected function openEditQuote(array $quote)
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
    }

    /**
     * Update quote on Storefront
     *
     * @return array
     */
    protected function frontUpdate()
    {
        if ($this->updateData['frontQtys']) {
            $this->quoteFrontendGrid->open();
            $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
            $this->quoteFrontendView->getQuoteDetails()->updateQuoteProductsQty($this->updateData['frontQtys']);
            $this->updateData['historyLog'][] = 'Status';
            foreach ($this->products as $product) {
                $this->updateData['historyLog'][] = $product->getName();
            }
        }

        return $this->updateData;
    }

    /**
     * Send quote on Storefront
     *
     * @return array
     */
    protected function frontSend()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->send();

        return [
            'frontLock' => true
        ];
    }

    /**
     * Update quote in admin
     *
     * @return array
     */
    protected function adminComment()
    {
        if (isset($this->messages['comment-admin'])) {
            $this->negotiableQuoteGrid->open();
            $filter = ['quote_name' => $this->quote['quote-name']];
            $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
            $this->negotiableQuoteView->getQuoteDetails()->updateComment($this->messages['comment-admin']);
            $this->updateData['historyLog'][] = 'Comment';
            $this->updateData['historyLog'][] = 'Status';
        }

        $this->negotiableQuoteView->getQuoteDetailsActions()->send();

        return [
            'frontStatus' => 'UPDATED',
            'frontLock' => false,
            'disabledButtonsFront' => [],
            'adminStatus' => 'Submitted',
            'adminLock' => true
        ];
    }

    /**
     * Send comment
     *
     * @return array
     */
    protected function frontComment()
    {
        if (isset($this->messages['comment'])) {
            $this->quoteFrontendGrid->open();
            $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
            $this->quoteFrontendView->getQuoteDetails()->updateComment($this->messages['comment']);
            $this->updateData['historyLog'][] = 'Comment';
            $this->updateData['historyLog'][] = 'Status';
        }

        $this->quoteFrontendView->getQuoteDetails()->send();

        return [
            'frontStatus' => 'SUBMITTED',
            'frontLock' => true,
            'adminStatus' => 'Open',
        ];
    }

    /**
     * Close quote on Storefront
     *
     * @return array
     */
    protected function frontClose()
    {
        $this->quoteFrontendGrid->open();
        $this->quoteFrontendGrid->getQuoteGrid()->openFirstItem();
        $this->quoteFrontendView->getQuoteDetails()->close();
        return [
            'frontStatus' => 'CLOSED',
            'frontLock' => false,
            'disabledButtonsFront' => ['checkout', 'send'],
            'adminStatus' => 'Closed',
            'adminLock' => false,
            'disabledButtonsAdmin' => ['saveAsDraft', 'decline', 'send'],
        ];
    }

    /**
     * Update quote in admin
     *
     * @return array
     */
    protected function adminUpdate()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->updateData['historyLog'][] = 'Status';
        if (isset($this->updateData['proposedShippingPrice'])) {
            $this->negotiableQuoteView
                ->getQuoteDetails()->fillProposedShippingPrice($this->updateData['proposedShippingPrice']);
            $this->updateData['historyLog'][] = 'Shipping Method';
            $this->updateData['historyLog'][] = 'Shipping Address';
        }
        if (isset($this->updateData['adminQtys'])) {
            $this->negotiableQuoteView->getQuoteDetails()->updateItems($this->updateData['adminQtys']);
            foreach ($this->products as $product) {
                $this->updateData['historyLog'][] = $product->getName();
            }
        }
        if (isset($this->updateData['expirationDate'])) {
            $this->negotiableQuoteView->getQuoteDetails()->fillExpirationDate($this->updateData['expirationDate']);
            $this->updateData['historyLog'][] = 'Expiration Date';
        }
        if (isset($this->updateData['discountType'])) {
            $this->negotiableQuoteView
                ->getQuoteDetails()
                ->fillDiscount($this->updateData['discountType'], $this->updateData['discountValue']);
        }
        $this->negotiableQuoteView->getQuoteDetailsActions()->send();
        $this->updateData['frontStatus'] = 'UPDATED';
        $this->updateData['disabledButtonsFront'] = [];
        $this->updateData['adminStatus'] = 'Submitted';
        $this->updateData['disabledButtonsAdmin'] = [];
        $this->updateData['adminLock'] = true;
        $this->updateData['disabledButtonsAdmin'] = ['saveAsDraft', 'decline', 'send'];

        return $this->updateData;
    }

    /**
     * Delete address from address book
     *
     * @return array
     */
    protected function adminDeleteDefaultAddress()
    {
        $this->objectManager
            ->create(\Magento\Customer\Test\TestStep\OpenCustomerOnBackendStep::class, ['customer' => $this->customer])
            ->run();
        $this->customerIndexEdit->getAddressesBlock()->openAddressesBlock();
        $this->customerIndexEdit->getEditAddressesBlock()->deleteDefaultAddress();
        $this->customerIndexEdit->getModalBlock()->acceptAlert();
        $this->customerIndexEdit->getPageActionsBlock()->save();

        return [];
    }

    /**
     * Add products by SKU
     *
     * @return array
     */
    protected function adminAddProductsBySku()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetails()->addProductsBySku($this->skuArray);

        return [];
    }

    /**
     * Configure complex product
     *
     * @return array
     */
    protected function configureComplexProduct()
    {
        $this->updateData['historyLog']['addedProductName'] =
            $this->negotiableQuoteView->getQuoteDetails()->getAddedProductName();
        $this->negotiableQuoteView->getQuoteDetails()->clickConfigureButton();
        if ($this->negotiableQuoteView->getQuoteConfigurablePopup()->isBundleSelectVisible()) {
            $this->negotiableQuoteView->getQuoteConfigurablePopup()->selectOption();
        }
        $this->negotiableQuoteView->getQuoteConfigurablePopup()->confirm();

        return [];
    }

    /**
     * Configure complex product in items quoted block
     *
     * @return array
     */
    protected function configureFromItemsQuoted()
    {
        $this->negotiableQuoteGrid->open();
        $filter = ['quote_name' => $this->quote['quote-name']];
        $this->negotiableQuoteGrid->getGrid()->searchAndOpen($filter);
        $this->negotiableQuoteView->getQuoteDetails()->clickConfigureButton();
        $this->negotiableQuoteView->getQuoteConfigurablePopup()->updateQty($this->qty);
        $this->negotiableQuoteView->getQuoteConfigurablePopup()->confirm();
        $this->negotiableQuoteView->getQuoteDetails()->clickUpdateButton();
        $this->updateData['historyLog']['updatedProductName'] =
            $this->negotiableQuoteView->getQuoteDetails()->getUpdatedProductName();

        return [];
    }

    /**
     * Add products to quote
     *
     * @return array
     */
    protected function addProductsToQuote()
    {
        $this->negotiableQuoteView->getQuoteDetails()->clickAddProductsToQuote();

        return [];
    }

    /**
     * Remove failed products from quote
     *
     * @return array
     */
    protected function removeFailedProducts()
    {
        $this->negotiableQuoteView->getQuoteDetails()->removeProducts();

        return [];
    }

    /**
     * Clean cache in admin panel
     *
     * @return void
     */
    protected function cleanCache()
    {
        $this->adminCache->open();
        $this->adminCache->getActionsBlock()->flushMagentoCache();
        $this->adminCache->getMessagesBlock()->waitSuccessMessage();
    }

    /**
     * Logout customer from Storefront account.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->logoutCustomerOnFrontendStep->run();
        if ($this->taxRule) {
            $this->objectManager->create(\Magento\Tax\Test\TestStep\DeleteAllTaxRulesStep::class, [])->run();
        }
        if ($this->salesRule) {
            $this->objectManager->create(\Magento\SalesRule\Test\TestStep\DeleteAllSalesRuleStep::class, [])->run();
        }
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $this->configData, 'rollback' => true]
        )->run();
    }
}
