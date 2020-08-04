<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Session;

use Progressive\PayWithProgressive\Model\Config;

/**
 * Class ConfigInfo
 * Helper class that provides access to local and global config
 *
 * @package Progressive\PayWithProgressive\Helper
 */
class ConfigInfo extends AbstractHelper
{
    protected $_scopeConfig; // scopeConfig
    protected $_localConfig;
    protected $_mode; // Demo, Production
    protected $_active;
    protected $_customerSession;
    private $_activePublicKey;
    private $_activePrivateKey;

    /**
     * ConfigInfo constructor.
     * @param Context $context
     * @param Config $localConfig
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        Config $localConfig,
        Session $customerSession
    )
    {
        parent::__construct($context);
        $this->_localConfig = $localConfig;
        $this->_customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->_mode = $this->_localConfig->getValue("mode");
        $this->_active = $this->_localConfig->getValue("active");
    }

	public function getTaxExempt()
	{
		return $this->_localConfig->getValue("tax_exempt");
	}

    /**
     * getApiUrl
     *
     * Returns the value of the api url based on mode, or null if module is not active
     *  as a nice side-effect it also collects other 'mode' dependent information
     *
     * @return string|null
     */
    public function getApiUrl()
    {
        $url = null;

        $this->_mode = $this->_localConfig->getValue("mode");
        $this->_active = $this->_localConfig->getValue("active");

        if (strtolower($this->_mode) == "demo") {
            $url = $this->_localConfig->getValue("demo_api_url");
            $this->_activePublicKey = $this->_localConfig->getValue("public_api_key_demo");
            $this->_activePrivateKey = $this->_localConfig->getValue("private_api_key_demo");
        } else {
            $url = $this->_localConfig->getValue("production_api_url");
            $this->_activePublicKey = $this->_localConfig->getValue("public_api_key_production");
            $this->_activePrivateKey = $this->_localConfig->getValue("private_api_key_production");
        }

        return $url;
    }

    /**
     * getBasicAuth
     *
     * Returns the base64 encoded value of the correct public and private key
     * to use with Authorization: Basic
     *
     * @return string
     */
    public function getBasicAuth()
    {
        return base64_encode($this->_activePublicKey . ":" . $this->_activePrivateKey);
    }

    /**
     * getCustomerId
     *
     * returns null if customer is not logged in yet
     *
     * @return null|int
     *
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerGroupId();
    }

    /**
     * getCustomerEmail
     *
     * @returns null|string
     */
    public function getCustomerEmail()
    {
        return $this->_customerSession->getCustomer()->getEmail();
    }

    /**
     * getCustomerName
     *
     * Returns null if no one is logged in, customer name if otherwised
     *
     * @return null|string
     */
    public function getCustomerName()
    {
        return $this->_customerSession->getName();
    }

    /**
     * getLoggedIn
     *
     * @return boolean
     */
    public function getLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * getCustomerData
     *
     * @return array
     *
     */
    public function getCustomerData()
    {
        $data = $this->_customerSession->getData();
        print_r($data);
        return $data;
    }

    /**
     * getMode
     *
     * @return string current mode of plugin
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * getMerchantId
     *
     * @return string configured merchant ID
     */
    public function getMerchantId()
    {
        return $this->_localConfig->getValue("merchant_id");
    }

    /**
     * getStoreId
     *
     * @return string configured store ID
     */
    public function getStoreId()
    {
        return $this->_localConfig->getValue("store_id");
    }

}
