<?php
namespace Progressive\PayWithProgressive\Observer;

use Magento\Framework\Event\ObserverInterface;

class ExemptSalesTax implements ObserverInterface
{
        public function execute(\Magento\Framework\Event\Observer $observer)
	{
                $order = $observer->getOrder();
                if($order->getPayment()->getMethod() != "progressive_gateway")
                        return;
                $taxAmount = $order->getTaxAmount(0);
                $baseTaxAmount = $order->getBaseTaxAmount();

                $order->setTaxAmount(0);
                $order->setBaseTaxAmount(0);

                $order->setGrandTotal($order->getGrandTotal() -$taxAmount);
                $order->setBaseGrandTotal($order->getBaseGrandTotal() - $baseTaxAmount);

                $order->save();
	}
}
