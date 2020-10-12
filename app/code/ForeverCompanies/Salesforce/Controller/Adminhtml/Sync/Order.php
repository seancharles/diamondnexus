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
 * Class Order Controller
 *
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Sync
 */
class Order extends Action
{
    /**
     * @var Sync\Order
     */
    protected $order;

    /**
     * Customer constructor
     * @param Context $context
     * @param Sync\Order $order
     */
    public function __construct(
        Context $context,
        Sync\Order $order
    ) {
        $this->order = $order;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        try{
          $orderIncrementId = $this->getRequest()->getParam('id');
          if ($orderIncrementId){
              $this->order->sync($orderIncrementId);
              $this->messageManager->addSuccess(
                  __('Order is synced successfullys')
              );
          } else {
              $this->messageManager->addNotice(
                  __('No order has been selected')
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
           'ForeverCompanies_Salesforce::config_salesforce'
       );
    }
}
