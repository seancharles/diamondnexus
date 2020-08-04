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

use Magento\Backend\Model\Session;
use Progressive\PayWithProgressive\Api\CheckoutPaymentManagerInterface;

class CheckoutPaymentManager implements CheckoutPaymentManagerInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $_quoteManager;

    public function __construct(
        Session $session
    )
    {
        $this->_session = $session;
    }

    /**
     * Init CheckoutPayment Manager
     *
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initPayment()
    {
        $quote = $this->_session->getQuote();
        if ($quote->getId()) {
            $payment = $quote->getPayment();
            $data['method'] = \Progressive\PayWithProgressive\Model\Ui\ConfigProvider::CODE;
            $payment->importData($data);
            $quote->save();
            return true;
        }
        return false;
    }

    /**
     * Verify payment method
     *
     * @return bool
     */
    public function verifyProgressive()
    {
        $quote = $this->_session->getQuote();
        if ($quote !== null) {
            if ($quote->getId()) {
                $payment = $quote->getPayment();
                if ($payment->getData('method') == \Progressive\PayWithProgressive\Model\Ui\ConfigProvider::CODE) {
                    $payment->setData('method', null);
                    return true;
                }
            }
        }
        return false; // TODO - need to figure out why getQuote() isn't working on storedemo
    }

}