<?php
namespace ForeverCompanies\Salesforce\Controller\Adminhtml\Debug;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use ForeverCompanies\Salesforce\Model\QueueFactory;
use ForeverCompanies\Salesforce\Model\Sync\Order;

class Index extends Action
{
    /**
     * @var orderRepository
     */
    protected $orderRepository;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var \ForeverCompanies\Salesforce\Model\Sync\Order
     */
    protected $orderModel;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        QueueFactory $queueFactory,
        Order $syncOrder,
        Context $context
    ) {
        $this->orderRepository = $orderRepository;
        $this->queueFactory = $queueFactory;
        $this->syncOrder  = $syncOrder;
        parent::__construct($context);
    }

    public function execute()
    {
        //$this->addToQueue('sales/order', 526398);
        
        $order = $this->orderRepository->get(526398);
        
        $salesforceId = $order->getData('customer_sf_acctid');
        $orderSalesforceId = $order->getData('sf_orderid');
        $orderSalesforceGuestId = $order->getData('sf_order_itemid');
        $increment_id = $order->getIncrementId();

        echo $increment_id . "<br />";

        //$orderId = $this->syncOrder->createOrder('Order', $result, $order->getIncrementId());
        
        $sfOrderId = $this->syncOrder->sync(null, '5000000010', true, null);

        echo $sfOrderId . "<br />";

        /*
        if ($salesforceId) {

            if (!$orderSalesforceId && $orderSalesforceId == null) {
                     $this->syncOrder->sync($salesforceId, $increment_id, false, "");
            } elseif ($orderSalesforceId && !$orderSalesforceGuestId) {
                      $this->syncOrder->sync($salesforceId, $increment_id, false, $orderSalesforceId);
            }

        } elseif (!$orderSalesforceGuestId && $orderSalesforceGuestId == null && !$orderSalesforceId) {
            $this->syncOrder->sync($salesforceId, $increment_id, true, "");
        }
        */
        
        echo "Order Sent to SalesForce";
    }
    
    public function addToQueue($type, $entityId)
    {
        /** add to queue mode */
        $queue = $this->queueFactory->create()
            ->getCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('entity_id', $entityId)
            ->getFirstItem();

        if ($queue->getId()) {

            /** Creditmemo existed in queue */
            $queue = $this->queueFactory->create()->load($queue->getId());
            $queue->setEnqueueTime(time());
            $queue->save();
            return;
        }
        $queue = $this->queueFactory->create();
        $data = [
            'type' => $type,
            'entity_id' => $entityId,
            'enqueue_time' => time(),
            'priority' => 1,
        ];
        $queue->setData($data);
        $queue->save();
        return;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('ForeverCompanies_Salesforce::queue');
    }
}
