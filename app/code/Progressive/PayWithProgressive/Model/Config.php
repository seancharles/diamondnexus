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

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{

    const KEY_ACTIVE = 'active';
    const KEY_MODE = 'mode';
    const KEY_PUBLIC_KEY_DEMO = 'public_api_key_demo';
    const KEY_PRIVATE_KEY_DEMO = 'private_api_key_demo';
    const KEY_PUBLIC_KEY_PRODUCTION = 'public_api_key_production';
    const KEY_PRIVATE_KEY_PRODUCTION = 'private_api_key_production';
	const KEY_TAX_EXEMPT = 'tax_exempt';

    const API_URL_DEMO = 'https://demo.progressivelp.com/ecomapi';
    const API_URL_PRODUCTION = 'https://demo.progressivelp.com/ecomapi';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_methodCode = 'progressive_gateway';

    /**
     * Scope configuration object
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Current store id
     *
     * @var int
     */
    protected $storeId;

    /**
     * Path pattern
     *
     * @var $pathPattern
     */
    protected $_pathPattern;

    /**
     * Inject scope and store manager object
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        $value = null;
        $path = $this->getConfigPathToField($field);
        if ($path !== null) {
            $value = $this->scopeConfig->getValue(
                $path,
                ScopeInterface::SCOPE_WEBSITE
            );
        }
        return $value;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->_methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->_pathPattern = $pathPattern;
    }

    protected function getConfigPathToField($fieldName)
    {
        if ($this->_pathPattern) {
            return sprintf($this->_pathPattern, $this->_methodCode, $fieldName);
        }
        return "payment/{$this->_methodCode}/{$fieldName}";
    }

    /**
     * Get public API key based on mode
     *
     * @return mixed
     */
    protected function getPublicApiCode()
    {
        if ($this->getApiMode()->strtolower() === 'demo') {
            return $this->getValue('public_api_key_demo');
        } else {
            return $this->getValue('public_api_key_production');
        }
    }

    /**
     * Get public api url base on mode
     *
     * @return mixed
     */
    protected function getApiUrl()
    {
        if ($this->getApiMode()->strtoLower() === 'demo') {
            return $this->getValue('demo_api_url');
        } else {
            return $this->getValue('production_api_url');
        }
    }

    /**
     * Get the current configured mode
     *
     * @return mixed
     */
    private function getApiMode()
    {
        return $this->getValue("mode");
    }

	private function getTaxExempt()
	{
		return $this->scopeConfig->getValue("payment/progressive_gateway/tax_exempt");
	}

    /**
     * Get the configured merchant ID
     *
     * @return mixed
     */
    private function getMerchantId()
    {
        return $this->scopeConfig->getValue("payment/progressive_gateway/merchant_id");
    }

    /**
     * Get the configured store ID
     *
     * @return mixed
     */
    private function getStoreId()
    {
        return $this->scopeConfig->getValue("payment/progressive_gateway/store_id");
    }

}
