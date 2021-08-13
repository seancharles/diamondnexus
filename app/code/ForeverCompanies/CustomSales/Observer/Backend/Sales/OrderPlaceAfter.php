<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Observer\Backend\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Backend\Model\Session as AdminSession;

class OrderPlaceAfter implements ObserverInterface
{
    protected $session;

    public function __construct(
        AdminSession $adminS
    ) {
        $this->session = $adminS;
    }

    public function execute(
        Observer $observer
    ) {
        $order = $observer->getData('order');
        
        $statusString = $this->session->getStatus();
        $exchangeString = $this->session->getIsExchange();
        
        if ($statusString !== false) {
            $order->setData('status', $statusString);
            $order->setData('state', $statusString);
            $order->setData('quote_expiration_date', date('Y-m-d', strtotime('+30 day')));
        }
        if ($exchangeString !== false) {
            $order->setData('is_exchange', $exchangeString);
        }
        $this->session->unsStatus();
        $this->session->unsIsExchange();
    }
}
