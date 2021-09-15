<?php

namespace ForeverCompanies\AdminOrderFixes\Model;

use Magento\Sales\Model\Order as OrigOrder;

class Order extends OrigOrder
{
    public function canCancel()
    {
        return true;
    }
    
    public function registerCancellation($comment = '', $graceful = true)
    {
        $state = self::STATE_CANCELED;
        foreach ($this->getAllItems() as $item) {
            $item->cancel();
        }
        
        $this->setSubtotalCanceled($this->getSubtotal() - $this->getSubtotalInvoiced());
        $this->setBaseSubtotalCanceled($this->getBaseSubtotal() - $this->getBaseSubtotalInvoiced());
        
        $this->setTaxCanceled($this->getTaxAmount() - $this->getTaxInvoiced());
        $this->setBaseTaxCanceled($this->getBaseTaxAmount() - $this->getBaseTaxInvoiced());
        
        $this->setShippingCanceled($this->getShippingAmount() - $this->getShippingInvoiced());
        $this->setBaseShippingCanceled($this->getBaseShippingAmount() - $this->getBaseShippingInvoiced());
        
        $this->setDiscountCanceled(abs($this->getDiscountAmount()) - $this->getDiscountInvoiced());
        $this->setBaseDiscountCanceled(abs($this->getBaseDiscountAmount()) - $this->getBaseDiscountInvoiced());
        
        $this->setTotalCanceled($this->getGrandTotal() - $this->getTotalPaid());
        $this->setBaseTotalCanceled($this->getBaseGrandTotal() - $this->getBaseTotalPaid());
        
        $this->setState($state)
        ->setStatus($this->getConfig()->getStateDefaultStatus($state));
        if (!empty($comment)) {
            $this->addStatusHistoryComment($comment, false);
        }
       
        return $this;
    }
}