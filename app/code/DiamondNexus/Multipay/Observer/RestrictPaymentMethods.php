<?php

namespace DiamondNexus\Multipay\Observer;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;
use Magento\Backend\Model\Session\Quote as adminQuoteSession;

class RestrictPaymentMethods implements ObserverInterface
{
    protected $_state;
    protected $_session;
    protected $_quote;

    private $_i;


    public function __construct(
        State $state,
        Session $checkoutSession,
        adminQuoteSession $adminQuoteSession
    ) {
        $this->_state = $state;
        if ($state->getAreaCode() == Area::AREA_ADMINHTML) {
            $this->_session = $adminQuoteSession;
        } else {
            $this->_session = $checkoutSession;
        }
        $this->_quote = $this->_session->getQuote();
        $this->_i = 1;
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        //Code of Current Payment Method--
        $code = $observer->getEvent()->getMethodInstance()->getCode();

        if ($this->_state->getAreaCode() == Area::AREA_ADMINHTML && $code != 'multipay') {
            $checkResult = $observer->getEvent()->getResult();
            //this is disabling the payment method at both checkout page in front-end only
            $checkResult->setData('is_available', false);
        }

        //file_put_contents('/home/admin/html/var/log/paymentMethods.log', $this->_i . " - " . $code . "\n", FILE_APPEND);
        //$this->_i++;
    }
}