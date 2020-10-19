<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Observer\Customer;

use ForeverCompanies\Salesforce\Observer\Customer\AbstractCustomer;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\Sync\Account;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
/**
 * Class Register
 */
class Register extends AbstractCustomer
{
    /**
     * Edit constructor.
     * @param QueueFactory $queueFactory
     * @param ScopeConfigInterface $config
     * @param Account $account
     */
    public function __construct(
        QueueFactory $queueFactory,
        ScopeConfigInterface $config,
        Account $account
    ) {
        parent::__construct($queueFactory, $config, $account);
    }

    /**
     * Trigger Customer register
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getCustomer();
        $this->syncAccount($customer);
    }
}
