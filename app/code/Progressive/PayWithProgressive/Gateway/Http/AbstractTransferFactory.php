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

namespace Progressive\PayWithProgressive\Gateway\Http;

use Progressive\PayWithProgressive\Gateway\Helper\Request\Action;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

/**
 * Class AbstractTransferFactory
 */
abstract class AbstractTransferFactory implements TransferFactoryInterface
{
    /**
     * Config
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Transfer builder
     *
     * @var TransferBuilder
     */
    protected $transferBuilder;

    /**
     * Action
     *
     * @var Action
     */
    protected $action;

    /**
     * Construct
     *
     * @param ConfigInterface $config
     * @param TransferBuilder $transferBuilder
     * @param Action $action
     */
    public function __construct(
        ConfigInterface $config,
        TransferBuilder $transferBuilder,
        Action $action
    ) {
        $this->config = $config;
        $this->transferBuilder = $transferBuilder;
        $this->action = $action;
    }

    /**
     * Get public API key
     *
     * @return string
     */
    protected function getPublicApiKey($storeId = null)
    {
        return $this->config->getValue('mode') == 'demo'
            ? $this->config->getValue('public_api_key_demo')
            : $this->config->getValue('public_api_key_production');
    }

    /**
     * Get private API key
     *
     * @return string
     */
    protected function getPrivateApiKey($storeId = null)
    {
        return $this->config->getValue('mode') == 'demo'
            ? $this->config->getValue('private_api_key_demo')
            : $this->config->getValue('private_api_key_production');
    }
}
