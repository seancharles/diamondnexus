<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use ForeverCompanies\Salesforce\Model\Sync;
use Magento\Framework\Controller\ResultFactory;


/**
 * Class Account Controller
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Sync
 */
class Account extends Action
{
    /**
     * @var Sync\Account
     */
    protected $_account;

    /**
     * Customer constructor
     * @param Context $context
     * @param Sync\Order $order
     */
    public function __construct(
        Context $context,
        Sync\Account $account
    ) {
        $this->_account = $account;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try{
            $customerId= $this->getRequest()->getParam('id');
            if ($customerId){
                $this->_account->sync($customerId, true);
                $this->messageManager->addSuccess(
                    __('Account is synced successfully')
                );
            } else {
                $this->messageManager->addNotice(
                    __('No account has been selected')
                );
            }
        } catch (\Exception $e){
            $this->messageManager->addError(
                __('Something happen during syncing process. Detail: ' . $e->getMessage())
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'ForeverCompanies_Salesforce::config_salesforcecrm'
        );
    }
}
