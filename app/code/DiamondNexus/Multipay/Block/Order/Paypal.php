<?php

namespace DiamondNexus\Multipay\Block\Order;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\Store\Model\ScopeInterface;

class Paypal extends AbstractPay
{

    /**
     * @return mixed|string
     */
    public function getClientId()
    {
        try {
            $id = $this->_storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue(Constant::CLIENT_XML, ScopeInterface::SCOPE_STORE, $id);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return mixed|string
     */
    public function getPaypalLogo()
    {
        try {
            $id = $this->_storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue('paypal/style/logo', ScopeInterface::SCOPE_STORE, $id);
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @return MessageInterface|MessageInterface[]
     */
    public function getErrors()
    {
        return $this->messageManager->getMessages()->getErrors();
    }
}
