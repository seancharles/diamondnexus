<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

//Front end config provider
//This data gets injected under the window.checkoutConfig.payment.progressive_gateway object

namespace Progressive\PayWithProgressive\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;

class AdditionalConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param ScopeConfig $scopeConfig
     */
    public function __construct(
        ScopeConfig $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    public function getConfig()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        return [
            'payment' => [
               'progressive_gateway' => [
                   'mode' => $this->scopeConfig->getValue('payment/progressive_gateway/mode', $storeScope),
                   'demo_api_url' => $this->scopeConfig->getValue('payment/progressive_gateway/demo_api_url', $storeScope),
                   'production_api_url' => $this->scopeConfig->getValue('payment/progressive_gateway/production_api_url', $storeScope),
                   'store_id' => $this->scopeConfig->getValue('payment/progressive_gateway/store_id', $storeScope),
                   'merchant_id' => $this->scopeConfig->getValue('payment/progressive_gateway/merchant_id', $storeScope),
				   'tax_exempt' => $this->scopeConfig->getValue('payment/progressive_gateway/tax_exempt', $storeScope)
                ]
            ]
        ];
    }
}