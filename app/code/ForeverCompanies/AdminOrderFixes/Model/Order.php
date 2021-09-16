<?php

namespace ForeverCompanies\AdminOrderFixes\Model;

use Magento\Sales\Model\Order as OrigOrder;

class Order extends OrigOrder
{
    public function canCancel()
    {
        return true;
    }
    
    public function canCreditmemo()
    {
        if ($this->hasForcedCanCreditmemo()) {
            return $this->getForcedCanCreditmemo();
        }
        
        if ($this->canUnhold() || $this->isPaymentReview() ||
            $this->getState() === self::STATE_CLOSED) {
                return false;
            }
            
            /**
             * We can have problem with float in php (on some server $a=762.73;$b=762.73; $a-$b!=0)
             * for this we have additional diapason for 0
             * TotalPaid - contains amount, that were not rounded.
             */
            $totalRefunded = $this->priceCurrency->round($this->getTotalPaid()) - $this->getTotalRefunded();
            if (abs($this->getGrandTotal()) < .0001) {
                return $this->canCreditmemoForZeroTotal($totalRefunded);
            }
            
            return $this->canCreditmemoForZeroTotalRefunded($totalRefunded);
    }
    
    private function canCreditmemoForZeroTotalRefunded($totalRefunded)
    {
        $isRefundZero = abs($totalRefunded) < .0001;
        // Case when Adjustment Fee (adjustment_negative) has been used for first creditmemo
        $hasAdjustmentFee = abs($totalRefunded - $this->getAdjustmentNegative()) < .0001;
        $hasActionFlag = $this->getActionFlag(self::ACTION_FLAG_EDIT) === false;
        if ($isRefundZero || $hasAdjustmentFee || $hasActionFlag) {
            return false;
        }
        
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
        
       
        $this->addStatusHistoryComment("", false);
        
        return $this;
    }
}