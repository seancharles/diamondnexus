<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Request;

use ForeverCompanies\Salesforce\Model\Queue;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config;

/**
 * Class Account
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Queue
 */
class Account extends Action
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $configInterface;

    /**
     * @var Config
     */
    protected $config;


    /**
     * @return QueueFactory
     */
    protected $queueFactory;

    /**
     * @var string
     */
    protected $type = Queue::TYPE_ACCOUNT;

    /**
     * @var int
     */
    protected $orderToInvoiceFlag;

    /**
     * Customer constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param Config $config
     * @param ScopeConfigInterface $configInterface
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        Config $config,
        ScopeConfigInterface $configInterface,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->config = $config;
        $this->configInterface = $configInterface;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customers = $this->customerFactory->create()
            ->getCollection();
        /** @var \Magento\Customer\Model\Customer $customer */
        foreach ($customers as $customer) {
            $queue = $this->queueFactory->create();
            if (!$queue->queueExisted($this->type, $customer->getId())) {
                $queue->enqueue($this->type, $customer->getId());
            }
        }
        $this->messageManager->addSuccess(
            __('All Accounts have been added to queue, you can delete items you do not want to sync or click Sync Now')
        );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('*/*/index'));
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::config_salesforcecrm');
    }
}
