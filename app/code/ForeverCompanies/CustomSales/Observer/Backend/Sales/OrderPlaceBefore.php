<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Observer\Backend\Sales;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\Backend\Model\Session as AdminSession;

class OrderPlaceBefore implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Collection
     */
    protected $userResource;
    
    protected $session;

    /**
     * OrderPlaceBefore constructor.
     * @param Session $authSession
     * @param Collection $userResource
     * @param Redirect $redirect
     */
    public function __construct(
        Session $authSession,
        Collection $userResource,
        AdminSession $adminS
    ) {
        $this->authSession = $authSession;
        $this->userResource = $userResource;
        $this->session = $adminS;
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
        $salesPersonId = $order->getData('sales_person_id');
        if ($salesPersonId == null || $salesPersonId == 0) {
            $salesPersonString = $this->session->getSalesPersonId();
            if ($salesPersonString == false) {
                $order->setData('sales_person_id', $user->getId());
            } else {
                $order->setData('sales_person_id', $salesPersonString);
            }
        } else {
            $connection = $this->userResource->getConnection();
            $select = $connection->select()
                ->from($this->userResource->getMainTable())
                ->where('user_id = ?', $salesPersonId);

            $data = $connection->fetchRow($select);
            if ($data == false) {
                $order->setData('sales_person_id', $user->getId());
            } else {
                if ($data['is_active'] == 0) {
                    $order->setData('sales_person_id', $user->getId());
                }
            }
        }
    }
}
