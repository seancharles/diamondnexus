<?php

namespace DiamondNexus\Multipay\Block\Order;

use DiamondNexus\Multipay\Model\Constant;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

class Paypal extends AbstractPay
{

    public function getClientId()
    {
        try {
            $id = $this->_storeManager->getStore()->getId();
            return $this->_scopeConfig->getValue(Constant::CLIENT_XML, ScopeInterface::SCOPE_STORE, $id);
        } catch (NoSuchEntityException $e) {

        }
    }

}
