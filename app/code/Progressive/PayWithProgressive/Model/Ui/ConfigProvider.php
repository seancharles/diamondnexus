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

namespace Progressive\PayWithProgressive\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ProductMetadataInterface;
use Progressive\PayWithProgressive\Model\Config as ConfigProgressive;


/**
 * Class ConfigProvider
 * Config provider for the payment method
 *
 * @package Progressive\PayWithProgressive\Model\Ui
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**#@+
     * Define constants
     */
    const CODE = 'progressive_gateway';
    const SUCCESS =  0;
    const FRAUD = 1;

    /**
     * Progressive config model
     *
     * @var \Progressive\PayWithProgressive\Model\Config
     */
    protected $progressiveConfig;

    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    protected $_config;

    /**
     * Injected url builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_productMetadata;

    /**
     * @var \Progressive\PayWithProgressive\Model\Config
     */
    protected $_configProgressive;

    /**
     * ConfigProvider constructor.
     * @param ConfigInterface $config
     * @param UrlInterface $urlInterface
     * @param CheckoutSession $checkoutSession
     * @param ProductMetadataInterface $productMetadata
     * @param ConfigProgressive $configProgressive
     */

    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlInterface,
        CheckoutSession $checkoutSession,
        ProductMetadataInterface $productMetadata,
        ConfigProgressive $configProgressive
    )
    {
        $this->_config = $config;
        $this->urlBuilder = $urlInterface;
        $this->_checkoutSession = $checkoutSession;
        $this->_productMetadata = $productMetadata;
        $this->_configProgressive = $configProgressive;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        self::SUCCESS => __('Success'),
                        self::FRAUD => __('Fraud')
                    ],
                    'apiKeyPublic' => $this->_configProgressive->getPublicApiKey(),
                    'apiUrl' => $this->_configProgressive->getApiUrl(),
                    'merchant' => [
                        'user_confirmation_url' => $this->urlBuilder
                            ->getUrl('progressive/payment/confirm', ['_secure' => true]),
                        'user_cancel_url' => $this->urlBuilder
                            ->getUrl('progressive/payment/cancel', ['_secure' => true]),
                        'user_confirmation_url_action' => 'POST'
                    ],
                    'config' => [
                        'financial_product_key' => null
                    ],
                    'redirectUrl' => $this->urlBuilder->getUrl('progressive/checkout/start', ['_secure' => true]),
                    'afterProgressiveConf' => $this->config->getValue('after_progressive_conf'),
                    'logoSrc' => $this->config->getValue('icon'),
                    'info' => $this->config->getValue('info'),
                    'visibleType' => $this->config->getValue('control') ? true: false
                ]
            ]
        ];
    }
}
