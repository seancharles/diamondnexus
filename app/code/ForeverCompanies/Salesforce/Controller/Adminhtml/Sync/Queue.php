<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Sync;

use Magento\Backend\App\Action;
use ForeverCompanies\Salesforce\Model\Sync;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ResourceModelConfig;
use Magento\Backend\App\Action\Context;
use ForeverCompanies\Salesforce\Model\Connector;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Controller\ResultFactory;


/**
 * Class Queue Controller
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Sync
 */
class Queue extends Action
{

    /**
     * @var Sync\Account
     */
    protected $_account;

    /**
     * @var Sync\Order
     */
    protected $_order;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ResourceModelConfig
     */
    protected $resourceConfig;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * Queue constructor.
     * @param Context $context
     * @param Sync\Account $account
     * @param Sync\Order $order
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelConfig $resourceConfig
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        Context $context,
        Sync\Account $account,
        Sync\Order $order,
        ScopeConfigInterface $scopeConfig,
        ResourceModelConfig $resourceConfig,
        TypeListInterface $cacheTypeList
    ) {
        parent::__construct($context);
        $this->_account = $account;
        $this->_order = $order;
        $this->scopeConfig    = $scopeConfig;
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function execute()
    {
        $response = 'empty';
        try {
            /** Turn off auto sync due to saving custom attributes */
            $autoSyncValues = $this->getAutoSyncValues();

            /** Reset auto sync settings */
            $this->setAutoSyncValues($autoSyncValues);

            /** refresh config cache */
            $this->cacheTypeList->cleanType('config');

            $response += $this->_account->syncAllQueue();
            $response += $this->_order->syncAllQueue();


            /** Reset auto sync settings */
            $this->setAutoSyncValues($autoSyncValues);

            /** refresh config cache */
            $this->cacheTypeList->cleanType('config');

            $this->messageManager->addSuccess(
                __('All items in queue are synced')
            );

        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something happen during syncing process. Detail: ' . $e->getMessage()
                    . '. Response Log: '.serialize($response))
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;

    }
    protected function getAutoSyncValues()
    {
        return [
            Connector::XML_PATH_SALESFORCE_ACCOUNT_ENABLE =>
                $this->scopeConfig->getValue(Connector::XML_PATH_SALESFORCE_ACCOUNT_ENABLE),
            Connector::XML_PATH_SALESFORCE_ORDER_ENABLE =>
                $this->scopeConfig->getValue(Connector::XML_PATH_SALESFORCE_ORDER_ENABLE)
        ];
    }

    protected function setAutoSyncValues($values = []){

      if (count($values) == 0){
          $this->resourceConfig->saveConfig(Connector::XML_PATH_SALESFORCE_ACCOUNT_ENABLE, 0, 'default', 0);
        $this->resourceConfig->saveConfig(Connector::XML_PATH_SALESFORCE_ORDER_ENABLE,'default',0);
      } else {
          foreach ($values as $key => $value) {
              $this->resourceConfig->saveConfig($key, $value, 'default', 0);
          }
      }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::config_salesforce');
    }
}
