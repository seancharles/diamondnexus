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

    /**
     * OrderPlaceBefore constructor.
     * @param Session $authSession
     * @param Collection $userResource
     */
    public function __construct(
        Session $authSession,
        Collection $userResource
    )
    {
        $this->authSession = $authSession;
        $this->userResource = $userResource;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    )
    {
        $order = $observer->getData('order');
        $user = $this->authSession->getUser();
        if ($user !== null) {
            $order->setData('loggeduser', $user->getUserName());
        }
        $salesPersonId = $order->getData('sales_person_id');
        if ($salesPersonId == null) {
            $order->setData('sales_person_id', $user->getId());
        } else {
            $connection = $this->userResource->getConnection();
            $select = $connection->select()
                ->from($this->userResource->getMainTable())
                ->where('user_id = ?', $salesPersonId);

            $data = $connection->fetchRow($select);
            if ($data == false) {
                $order->setData('sales_person_id', $user->getId());
            }
            if ($data['is_active'] == 0) {
                $order->setData('sales_person_id', $user->getId());
            }
        }
    }
}
