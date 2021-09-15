<?php

namespace ForeverCompanies\AdminOrderFixes\Observer;

use Magento\Framework\Event\ObserverInterface;

class CreditMemoSaveAfter implements ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {
      $order = $observer->getEvent()->getCreditmemo()->getOrder();
      
      if ($order->isCanceled() == 1) {
          $order->setStatus('canceled');
          $order->save();
      }
      return $observer;
  }
}