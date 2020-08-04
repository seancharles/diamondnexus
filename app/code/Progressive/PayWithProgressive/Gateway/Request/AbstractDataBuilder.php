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

namespace Progressive\PayWithProgressive\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Progressive\PayWithProgressive\Helper\EcomSystem;

/**
 * Class AbstractDataBuilder
 */
abstract class AbstractDataBuilder implements BuilderInterface
{
    /**#@+
     * Define constants
     */
    const LEASE_ID = 'lease_id';
    /**#@-*/

    /**
     * Config
     *
     * @var ConfigInterface
     */
    private $config;
    public $ecomSystem;

    /**
     * Constructor
     *
     * @param ConfigInterface $config
     * @param EcomSystem $ecomSystem
     */
    public function __construct(
        ConfigInterface $config,
        EcomSystem $ecomSystem
    ) {
        $this->config = $config;
        $this->ecomSystem = $ecomSystem;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    abstract public function build(array $buildSubject);
}
