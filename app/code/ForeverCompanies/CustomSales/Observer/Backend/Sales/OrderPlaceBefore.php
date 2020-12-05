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
use Magento\Store\App\Response\Redirect;
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
     * @var Redirect
     */
    protected $redirect;

    /**
     * OrderPlaceBefore constructor.
     * @param Session $authSession
     * @param Collection $userResource
     * @param Redirect $redirect
     */
    public function __construct(
        Session $authSession,
        Collection $userResource,
        Redirect $redirect
    ) {
        $this->authSession = $authSession;
        $this->userResource = $userResource;
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
        $user = $this->authSession->getUser();
        if ($user !== null) {
            $order->setData('loggeduser', $user->getUserName());
        }
        $salesPersonId = $order->getData('sales_person_id');
        $refererUrl = $this->redirect->getRefererUrl();
        if ($salesPersonId == null || $salesPersonId == 0) {
            $salesPersonString = stristr($refererUrl, 'sales_person_id/');
            $salesPersonString = str_replace('sales_person_id/', '', $salesPersonString);
            if ($salesPersonString == false) {
                $order->setData('sales_person_id', $user->getId());
            } else {
                $position = strpos($salesPersonString, "/");
                $order->setData('sales_person_id', substr($salesPersonString, 0, $position));
            }
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
