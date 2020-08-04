<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class SessionHelper extends AbstractHelper
{

    protected $_logger;
    protected $_customerName;
    protected $_ecomSystem;
    protected $_productRepository;

    /**
     * SessionHelper constructor.
     * @param Context $context
     * @param EcomSystem $eComSystem
     * @param ProductRepository $productRepository
     */

    public function __construct(
        Context $context,
        EcomSystem $eComSystem,
        ProductRepository $productRepository
    )
    {
        parent::__construct($context);

        $this->_logger = $context->getLogger();
        $this->_ecomSystem = $eComSystem;
        $this->_productRepository = $productRepository;
    }

    /**
     * updateLogin
     *
     * Injected into the CustomerLogin and CustomerLogout Observers
     * so we can indicate via the Session endpoint
     *
     * @param Customer $customer
     *
     */
    public function updateLogin(Customer $customer)
    {
        $billingAddress = array();

        if ($this->_customerName === null) {
            $this->_customerName = $customer->getName();

            $names = explode(" ", $this->_customerName);
            $address = $customer->getPrimaryBillingAddress();
            $token = $this->_ecomSystem->getClientToken();

            $session = array(
                'SessionId' => $token,
                'ClientToken' => $token,
                'PurchaseCountry' => 'United States',
                'PurchaseCurrency' => 'USD',
                'Locale' => null,
                'CustomerGivenName' => $names[0],
                'CustomerFamilyName' => $names[1],
                'CustomerEmail' => $customer->getEmail(),
                'CustomerTitle' => null,
                'BillingAddress' => array(
                    'Email' => $customer->getEmail()
                )
            );

            if ($address) {
                $billingAddress = array(
                    'Email' => $customer->getEmail(),
                    'Title' => $address->getPrefix(),
                    'Phone' => $address->getTelephone(),
                    'StreetAddress' => $address->getStreetFull(),
                    'StreetAddress2' => null,
                    'PostalCode' => $address->getPostcode(),
                    'City' => $address->getCity(),
                    'Region' => $address->getRegion(),
                    'Country' => $address->getCountry()
                );

                $session['BillingAddress'] = $billingAddress;
            }
            $this->_ecomSystem->putSession($session);
        }
    }

    /**
     *
     */
    public function updateLogout()
    {
        $session = array(
            'SessionId' => $this->_ecomSystem->getClientToken(),
            'ClientToken' => $this->_ecomSystem->getClientToken()
        );
        // TODO add this back when the endpoint is done
        // $this->_ecomSystem->deleteSession($session);
    }
}