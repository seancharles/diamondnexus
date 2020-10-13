<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Request;

use ForeverCompanies\Salesforce\Model\Queue;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Order
 * @package ForeverCompanies\Salesforce\Controller\Adminhtml\Queue
 */
class Order extends \Magento\Backend\App\Action
{
    /**
     * @var string
     */
    protected $orderFactory;

    /**
     * @return QueueFactory
     */
    protected $queueFactory;

    /**
     * @var string
     */
    protected $type = Queue::TYPE_ORDER;

    /**
     * @var int
     */
    protected $orderToInvoiceFlag;

    /**
     * Order constructor.
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfigInterface,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $orders = $this->orderFactory->create()->getCollection();
        /** $var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order){
            $queue = $this->queueFactory->create();
            if (!$queue->queueExisted($this->type, $order->getIncrementId())){
                $queue->enqueue($this->type, $order->getIncrementId());
            }
        }
        $this->messageManager->addSuccess(
            __('All Orders have been added to queue, you can delete items you do not want to sync or click Sync Now')
        );
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('*/*/index'));
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::config_salesforce');
    }
}
