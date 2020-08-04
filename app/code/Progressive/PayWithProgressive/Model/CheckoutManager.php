<?php

/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com).
 * Modified by Prog Leasing, LLC. Copyright (c) 2018, Prog Leasing, LLC.
 */

namespace Progressive\PayWithProgressive\Model;

use Braintree\Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Progressive\PayWithProgressive\Helper\EcomSystem;
use Progressive\PayWithProgressive\Api\CheckoutManagerInterface;

class CheckoutManager implements CheckoutManagerInterface
{

    /**
     * Gift card id cart key
     *
     * @var string
     */
    const ID = 'i';

    /**
     * Gift card amount cart key
     *
     * @var string
     */
    const AMOUNT = 'a';

    /**
     * Injected checkout session
     *
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Injected model quote
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * Injected repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Product metadata
     *
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Module resource
     *
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $moduleResource;

    /**
     * @var \Progressive\PayWithProgressive\Model\Config
     */
    protected $config;

    /**
     * @var EcomSystem \Progressive\PayWithProgressive\Helper\EcomSystem
     */
    protected $ecomSystem;

    /**
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    private $compositeId;

    /**
     * Initialize progressive checkout
     *
     * @param Config $config
     * @param Session                                    $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductMetadataInterface                   $productMetadata
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ObjectManagerInterface                     $objectManager
     * @param EcomSystem                                 $ecomSystem
     */
    public function __construct(
        Config $config,
        Session $checkoutSession,
        CartRepositoryInterface $quoteRepository,
        ProductMetadataInterface $productMetadata,
        ResourceInterface $moduleResource,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        EcomSystem $ecomSystem
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $this->checkoutSession->getQuote();
        $this->quoteRepository = $quoteRepository;
        $this->productMetadata = $productMetadata;
        $this->moduleResource = $moduleResource;
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->ecomSystem = $ecomSystem;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initCheckout()
    {
        // Grab totals
        $this->quote->collectTotals();
        $this->quote->reserveOrderId();
        $orderIncrementId = $this->quote->getReservedOrderId();
        $discountAmount = $this->quote->getBaseSubtotal() - $this->quote->getBaseSubtotalWithDiscount();
        $shippingAddress = $this->quote->getShippingAddress();

        $response = [];
        try {
            $country = $this
                ->quote
                ->getBillingAddress()
                ->getCountry();
            $result = $this->quote
                ->getPayment()
                ->getMethodInstance()
                ->canUseForCountry($country);
            if (!$result) {
               throw new \Magento\Framework\Exception\LocalizedException(
                   __('Your billing country is not allowed by Progressive')
               );
            }
        } catch (Exception $e) {
            return $e->getMessage();
        }

        if ($orderIncrementId) {
            $this->quoteRepository->save($this->quote);
            $response['order_increment_id'] = $orderIncrementId;
        }

        $response['order_amount'] = $this->quote->getGrandTotal();
        $response['tax_amount'] = $this->quote->getGrandTotal() - $this->quote->getSubtotal();

        /*
         * Some utility metadata
         */
        $sessionId = $this->ecomSystem->getClientToken();
        $baseApiUrl = $this->ecomSystem->getApiUrl();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $compositeId = $this->ecomSystem->getCompositeId();

        $response['metadata'] = [
            'platform_type' => $this->productMetadata->getName() . ' ' . $this->productMetadata->getEdition(),
            'platform_version' => $this->productMetadata->getVersion(),
            'session_id' => $sessionId,
            'baseapi_url' => $baseApiUrl,
            'base_url' => $baseUrl,
            'composite_id' => $compositeId
        ];

        return json_encode($response);
    }
}
