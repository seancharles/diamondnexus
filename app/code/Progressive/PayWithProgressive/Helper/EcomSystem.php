<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\Session;

class EcomSystem extends AbstractHelper
{

    const COOKIE_NAME = 'progressive-token';
    const COOKIE_DURATION = 86400;

    protected $_logger;
    protected $_cookieManager;
    protected $_cookieMetadataFactory;
    protected $_config;
    protected $_baseUrl;
    private $_clientToken;
    private $_cookieMetadata;
    private $_sessionManager;
    private $checkoutSession;
    private $quote;
    private $compositeId;

    private $sessionEndpoint = "/session";
    private $authenticateEndpoint = "/account/authenticate";
    private $registerEndpoint = "/account/register";
    private $applicationEndpoint = "/session/{sessionId}/application";
    private $authorizationsEndpoint = "/orders/authorizations";
    private $getSessionEndpoint = "/session/{clientToken}";
    private $getUiEndpoint = "/content/ui";
    private $getLocationsEndpoint = "/locations/{query}";
    private $getApplicationEndpoint = "/{clientToken}/application";
    private $orderCaptureEndpoint = "/orders/{orderId}/captures"; // Can be used for post too
    private $getLocationsDetailEndpoint = "/locations/details/{locationId}";
    private $deliveryConfirmationEndpoint = "/confirmdelivery";
	private $merchantConfigurationEndpoint = "/eCommMerchManageOrch/api/merchantConfig";

    /**
     *
     * {@inheritdoc}
     *
     * @param Context $context
     * @param ConfigInfo $configInfo
     */
    public function __construct(
        Context $context,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        ConfigInfo $configInfo,
        Session $checkoutSession
    )
    {
        parent::__construct($context);

        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->_sessionManager = $sessionManager;
        $this->_config = $configInfo;
        $this->_logger = $context->getLogger();
        $this->_baseUrl = $this->_config->getApiUrl();
        $this->checkoutSession = $checkoutSession;
        $this->quote = $checkoutSession->getQuote();
    }

    /**
     * setClientToken
     *
     * Create custom key,value pair in customer session for global storage
     *
     * @param string $value
     */
    public function setClientToken($value)
    {
        $this->_clientToken = $value;

        $this->_cookieMetadata = $this->_cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION)
            ->setHttpOnly(true)
            ->setPath('/');

        try {
            $this->_cookieManager->setPublicCookie(
                self::COOKIE_NAME,
                $value,
                $this->_cookieMetadata
            );
        } catch (Exception $e) {
            $this->_logger->debug("caught exception in setPublicCookie: " . $e->getMessage());
        }
    }

    /**
     * getClientToken
     *
     * Retrieve custom key,value from global storage in customer session
     *
     * @return string|null
     */
    public function getClientToken()
    {
        // Try and get clientToken from cookie
        $clientToken = $this->_cookieManager->getCookie($this::COOKIE_NAME);

        $this->_logger->debug("getClientToken from cookie: " . $clientToken);

        // If null then we either haven't retrieved it yet, or it didn't store in the cookie
        // Let's ask the API for it again
        if ($clientToken === null) {
            $payload = array();
            $result = $this->postSession($payload);
            if (!$result) {
                return null;
            }
            $clientToken = $result['sessionId'];
            $this->setClientToken($clientToken);
        }
        return $clientToken;
    }

    /**
     * getCompositeId
     *
     * Generate custom id from merchant id & cart id
     *
     * @return string|null
     */
    public function getCompositeId()
    {
        $compositeId = $this->_config->getMerchantId() . $this->quote->getId();
        return $compositeId;
    }

    /**
     * getCompositeId
     *
     * Get merchant id
     *
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->_config->getMerchantId();
    }

    /**
     * deleteClientToken
     *
     * Remove client token from storage
     *
     */
    public function deleteClientToken()
    {
        try {
            if ($this->_cookieManager->getCookie($this::COOKIE_NAME)) {
                $metadata = $this->_cookieMetadataFactory->createCookieMetadata();
                $metadata->setPath('/');
                $this->_cookieManager->deleteCookie($this::COOKIE_NAME, $metadata);
            }
        } catch (Exception $e) {
            $this->_logger->debug("Exception in EcomSystem::deleteClientToken: " . $e->getMessage());
        }
    }

    /**
     * getApiUrl
     *
     * Helper for js
     *
     * @return null|string
     */
    public function getApiUrl()
    {
        return $this->_baseUrl;
    }

    /*
     * *******************
     * Endpoint Services
     * *******************
     */

    /**
     * postAuthentication
     *
     * @param array $payload
     * @return number
     */
    public function postAuthenticate($payload)
    {
        $endpoint = $this->_baseUrl . $this->authenticateEndpoint;
        return ($this->post($endpoint, $payload));
    }

    /**
     * postRegistration
     *
     * @param array $payload
     * @return number
     */
    public function postRegister($payload)
    {
        $endpoint = $this->_baseUrl . $this->registerEndpoint;
        return ($this->post($endpoint, $payload));
    }

    /**
     * postApplication
     *
     * @param array $payload
     * @return number
     */
    public function postApplication($payload)
    {
        $endpoint = $this->_baseUrl . $this->registerEndpoint;
        return ($this->post($endpoint, $payload));
    }

    /**
     * postSession
     *
     * @param array $payload
     * @return number
     */
    public function postSession($payload)
    {
        $endpoint = $this->_baseUrl . $this->sessionEndpoint;
        return ($this->post($endpoint, $payload));
    }

    /**
     * putSession
     *
     * @param array $payload
     * @return number
     */
    public function putSession($payload)
    {
        $endpoint = $this->_baseUrl . $this->sessionEndpoint;
        return ($this->post($endpoint, $payload, false));
    }

    /**
     * deleteSession
     *
     * @param array $payload
     * @return number
     */
    public function deleteSession($payload)
    {
        $endpoint = $this->_baseUrl . $this->sessionEndpoint;
        return ($this->delete($endpoint, $payload));
    }

    /**
     * postAccountAuthentication
     *
     * @param array $payload
     * @return number
     */
    public function postAccountAuthenticate($payload)
    {
        $endpoint = $this->_baseUrl . $this->authenticateEndpoint;
        return ($this->post($endpoint, $payload, false));
    }

    /**
     * postAccountRegistration
     *
     * @param array $payload
     * @return number
     */
    public function postAccountRegister($payload)
    {
        $endpoint = $this->_baseUrl . $this->registerEndpoint;
        return ($this->post($endpoint, $payload, false));
    }

    /**
     * postSessionApplication
     *
     * @param array $payload
     * @return number
     */
    public function postSessionApplication($payload)
    {
        $realEndpoint = null;
        $sessionId = $this->getClientToken();
        $endpoint = $this->_baseUrl . $this->applicationEndpoint;
        if (strpos($endpoint, '{') === TRUE) {
           $realEndpoint = preg_replace('#\{.*?\}#s', $sessionId, $endpoint);
        }
        return ($this->post($realEndpoint, $payload, false));
    }


    /**
     * postOrdersCapture
     *
     * @param array $payload
     * @param string $orderId
     * @return number
     */
    public function postOrdersCapture($payload, $orderId)
    {
        $endpoint = $this->_baseUrl . $this->orderCaptureEndpoint;

        if (strpos($endpoint, '{') !== FALSE) {
            $endpoint = preg_replace('#\{.*?\}#s', $orderId, $endpoint);
        }
        return ($this->post($endpoint, $payload));
    }

    /**
     * getSession
     *
     * @param string $clientToken
     * @return array
     */
    public function getSession($clientToken)
    {
        $endpoint = $this->_baseUrl . $this->getSessionEndpoint;
        return ($this->get($endpoint, $clientToken));
    }

    /**
     * getContentUi
     *
     * @return array
     */
    public function getContentUi()
    {
        $endpoint = $this->_baseUrl . $this->getUiEndpoint;
        return ($this->get($endpoint));
    }

    /**
     * getLocations
     *
     * @param string $query
     * @return array
     */
    public function getLocations($query)
    {
        $endpoint = $this->_baseUrl . $this->getLocationsEndpoint;
        return ($this->get($endpoint, $query));
    }

    /**
     * getLocationsDetail
     *
     * @param $locationId
     * @return array
     */
    public function getLocationsDetail($locationId)
    {
        $endpoint = $this->_baseUrl . $this->getLocationsDetailEndpoint;
        return ($this->get($endpoint, $locationId));
    }

    /**
     * getApplication
     *
     * @param string $clientToken
     * @return array
     */
    public function getApplication($clientToken)
    {
        $endpoint = $this->_baseUrl . $this->getApplicationEndpoint;
        return ($this->get($endpoint, $clientToken));
    }

    /**
     * @param array $payload
     * @return number
     */
    public function postDeliveryConfirmation($payload)
    {
        $endpoint = $this->_baseUrl . $this->deliveryConfirmationEndpoint;
        return ($this->post($endpoint, $payload, true));
    }

	/**
     * @param array $payload
     * @return number
     */
    public function postMerchantConfiguration($payload)
    {
        $endpoint =  str_replace(parse_url($this->_baseUrl, PHP_URL_PATH), '', $this->_baseUrl) . $this->merchantConfigurationEndpoint;
        return ($this->post($endpoint, $payload, true));
    }

    /**
     * post - post to endpoint
     *
     * @param string $url
     * @param array $data
     * @param bool $post
     *            if true this method will POST, if false it will PUT
     * @return mixed - if return code != 2XX then return code, else response as array
     */
    private function post($url, $data, $post = true)
    {
        $payload = null;
        $payload = json_encode($data, JSON_FORCE_OBJECT);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json",
					"api-version: 1", // required for MerchantManagement.Orchestrator
                    "Authorization: Basic " . $this->_config->getBasicAuth()));
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,$post ? "POST" : "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
		if(curl_error($ch))
		{
			$error = curl_error($ch);
			$this->_logger->debug(($post ? 'POST' : 'PUT') . "call to ".$url." returned error: .$error");
			curl_close($ch);
			return 0;
		}

		$resultData = json_decode($result, TRUE);

		curl_close($ch);

		return $resultData;
    }

    /**
     * get
     *
     * Return the response in an array
     *
     * @param string $url
     * @param string $arg
     * @return array
     */
    private function get($url, $arg = NULL)
    {
        $realUrl = null;

        if (strpos($url, '{') === TRUE) {
            if ($arg != NULL) {
                $realUrl = preg_replace('#\{.*?\}#s', $arg, $url);
            }
        }

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $realUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic " . $this->_config->getBasicAuth()));
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
		if(curl_error($ch))
		{
			$error = curl_error($ch);
			$this->_logger->debug("GET returned error: .$error");
			curl_close($ch);
			return 0;
        }

		$resultData = json_decode($result, TRUE);

		curl_close($ch);
		return $resultData;
    }

    /**
     * delete - post to endpoint
     *
     * @param string $url
     * @param array $data
     * @return number 0 is read error, 200 and above are result codes
     */
    private function delete($url, $data)
    {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic " . $this->_config->getBasicAuth()));
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"DELETE");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);

		$result = curl_exec($ch);
		
		if(curl_error($ch))
		{
			$error = curl_error($ch);
			$this->_logger->debug("DELETE returned error: .$error");
			curl_close($ch);
			return 0;
		}

		if ($this->getClientToken() != null)
			$this->setClientToken(null);

		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		return $httpCode;
    }
}

