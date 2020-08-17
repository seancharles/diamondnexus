<?php

/**
 * Copyright (c) 2018, Prog Leasing, LLC
 * Licensed under the Open Software License version 3.0
 */

namespace Progressive\PayWithProgressive\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Customer;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Progressive\PayWithProgressive\Api\LeaseConfirmationInterface;
use Magento\Framework\UrlInterface;
use \Psr\Log\LoggerInterface;

class LeaseConfirmation implements LeaseConfirmationInterface
{

    /** @var Http $_request */
    private $_request;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var Customer $_customerModel */
    protected $_customerModel;

    /** @var StoreManagerInterface $_storeManager */
    protected $_storeManager;

    /** @var CartManagementInterface */
    protected $_cartManager;

    /** @var UrlInterface */
    protected $_urlBuilder;


    /**
     * LeaseConfirmation constructor.
     * @param $context
     * @param $request
     * @param $storeManager
     * @param $customerModel
     * @param $cartManager
     * @param $urlBuilder
     */
    public function __construct(
        Context $context,
        Http $request, /**/
        StoreManagerInterface $storeManager, /**/
        CartManagementInterface $cartManager,
        Customer $customerModel,
        UrlInterface $urlBuilder
    )
    {
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->_customerModel = $customerModel;
        $this->_urlBuilder = $urlBuilder;
        $this->_cartManager = $cartManager;
        $this->_logger = $context->getLogger();
    }

    /**
     * @return bool|mixed
     */

    public function confirmLease()
    {
        // leaseId == transaction id for payment
        $leaseId = $this->_request->getParam("leaseID");
        $customerEmail = $this->_request->getParam("customerEmail");

        // setup for call to endpoint /v1/carts/{cartId}/order
        $options = array(
            "payment_Method" => array(
                "po_number" => $leaseId,
                "method" => "progressive_gateway",
                "additional_data" => array(
                    null
                ),
                "extension_attributes" => array(
                    "agreement_ids" => array(
                        $leaseId
                    )
                )
            )
        );

        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $this->_customerModel->setWebsiteId($websiteId);
        $this->_customerModel->loadByEmail($customerEmail);
        $customerId = $this->_customerModel->getId();

        try {
            $cart = $this->_cartManager->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $exception) {
            $this->_logger->debug("nosuchentity cart exception: " . $exception->getMessage());
            return false;
        }
        $cartId = $cart->getReservedOrderId();

        // Build request to close cart
        $targetUrl = 'http://localhost/rest/default/V1/carts/' . $cartId . '/order/';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $targetUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic " . $this->_config->getBasicAuth()));
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
        if(curl_error($ch))
		{
			$error = curl_error($ch);
			$this->_logger->debug(($post ? 'POST' : 'PUT') . " returned error: .$error");
			curl_close($ch);
			return 0;
        }

        return true;
    }

    /**
     * getAuthToken
     * retrieves correct API Bearer
     *
     * @return bool|string
     */
    private function getAuthToken()
    {
        $payload = array(
            'username' => 'admin',
            'password' => 'admin123'
        );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $targetUrl);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Accept: application/json"));
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_FORCE_OBJECT));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($ch);
		
        if(curl_error($ch))
		{
			$error = curl_error($ch);
			$this->_logger->debug("GetAuthToken returned error: .$error");
			curl_close($ch);
			return 0;
        }
        return json_decode($result, TRUE);
    }
}