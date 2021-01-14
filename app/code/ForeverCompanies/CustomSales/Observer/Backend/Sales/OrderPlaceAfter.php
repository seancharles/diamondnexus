<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Observer\Backend\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\App\Response\Redirect;

class OrderPlaceAfter implements ObserverInterface
{

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * OrderPlaceBefore constructor.
     * @param Redirect $redirect
     */
    public function __construct(
        Redirect $redirect
    ) {
        $this->redirect = $redirect;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        $order = $observer->getData('order');
        $refererUrl = $this->redirect->getRefererUrl();
        $statusString = stristr($refererUrl, 'status/');
        $statusString = str_replace('status/', '', $statusString);
        if ($statusString !== false) {
            $position = (int) strpos($statusString, "/");
            $order->setData('status', substr($statusString, 0, $position));
            $order->setData('state', substr($statusString, 0, $position));
            $order->setData('quote_expiration_date', date('Y-m-d', strtotime('+30 day')));
        }
    }
}
