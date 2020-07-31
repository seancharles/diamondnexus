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

namespace Progressive\PayWithProgressive\Gateway\Helper\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Progressive\PayWithProgressive\Helper\ConfigInfo;

/**
 * Class Action
 */
class Action
{
    /**#@+
     * Define constants
     */
    const API_CONFIRM_PATH = '/session/';
    /**#@-*/

    /**
     * Action
     *
     * @var string
     */
    private $action;

    /**
     * Config
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Config
     *
     * @var ConfigInterface
     */
    private $configInfo;

    /**
     * Constructor
     *
     * @param string $action
     * @param ConfigInterface $config
     * @param ConfigInfo $configInfo
     */
    public function __construct($action, ConfigInterface $config, ConfigInfo $configInfo)
    {
        $this->action = $action;
        $this->config = $config;
        $this->configInfo = $configInfo;
    }

    /**
     * Get request URL
     *
     * @param string $additionalPath
     * @return string
     */
    public function getUrl($additionalPath = '')
    {
        $gateway = $this->configInfo->getApiUrl();

        return trim($gateway, '/') . sprintf('%s%s', $this->action, $additionalPath);
    }
}
