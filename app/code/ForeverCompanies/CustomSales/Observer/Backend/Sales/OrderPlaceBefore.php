<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Observer\Backend\Sales;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;

class OrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * OrderPlaceBefore constructor.
     * @param Session $authSession
     */
    public function __construct(
        Session $authSession
    ) {
        $this->authSession = $authSession;
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
        $user = $this->authSession->getUser();
        if ($user !== null) {
            $order->setData('loggeduser', $user->getUserName());
        }
    }
}
