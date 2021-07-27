<?php

namespace ForeverCompanies\CustomApi\Controller\Adminhtml\Order;

use ForeverCompanies\CustomApi\Helper\ExtOrder;
use ForeverCompanies\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Controller\Adminhtml\Order as AdminOrder;
use Psr\Log\LoggerInterface;

use ShipperHQ\Shipper\Model\ResourceModel\Order\Detail;
use ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail;

class Order extends AdminOrder implements HttpPostActionInterface
{
    /**
     * Changes ACL Resource Id
     */
    const ADMIN_RESOURCE = 'Magento_Sales::hold';

    /**
     * @var GridDetail
     */
    protected $shipperResourceModel;

    /**
     * @var HistoryFactory
     */
    protected $orderHistoryFactory;

    /**
     * @var ExtOrder
     */
    protected $extOrder;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * Order constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param FileFactory $fileFactory
     * @param InlineInterface $translateInline
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $resultLayoutFactory
     * @param RawFactory $resultRawFactory
     * @param OrderManagementInterface $orderManagement
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     * @param Detail $shipperDetailResourceModel
     * @param GridDetail $shipperGridDetailResourceModel
     * @param HistoryFactory $orderHistoryFactory
     * @param ExtOrder $extOrder
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        FileFactory $fileFactory,
        InlineInterface $translateInline,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $resultLayoutFactory,
        RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        Detail $shipperDetailResourceModel,
        GridDetail $shipperGridDetailResourceModel,
        HistoryFactory $orderHistoryFactory,
        ExtOrder $extOrder,
        QueueFactory  $queueFactory
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $translateInline,
            $resultPageFactory,
            $resultJsonFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $orderManagement,
            $orderRepository,
            $logger
        );
        $this->shipperDetailResourceModel = $shipperDetailResourceModel;
        $this->shipperGridDetailResourceModel = $shipperGridDetailResourceModel;
        $this->orderHistoryFactory = $orderHistoryFactory;
        $this->extOrder = $extOrder;
        $this->queueFactory = $queueFactory;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $order = $this->_initOrder();
        if ($order) {
            /** @var Http $http */
            $http = $this->getRequest();
            $post = $http->getPostValue();
            $dispatchDate = $post['dispatch_date'];
            $deliveryDate = $post['delivery_date'];
            if ((strtotime($dispatchDate) > strtotime($deliveryDate))) {
                $this->messageManager->addErrorMessage('Dispatch date must be before Delivery date!');
            } else {
                $connection = $this->shipperDetailResourceModel->getConnection();
                $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
                    ->where('order_id = ?', $order->getEntityId())
                    ->order('id desc')
                    ->limit(1);
                $data = $connection->fetchRow($select);
                
                $changes = [];
                
                if ($data != false) {
                    if ($data['dispatch_date'] != $dispatchDate) {
                        $changes['dispatch_date'] = $dispatchDate;
                    }
                    if ($data['delivery_date'] != $deliveryDate) {
                        $changes['delivery_date'] = $deliveryDate;
                    }
                }
                
                if(count($changes) > 0) {
                    $this->messageManager->addSuccess('Delivery dates have been updated!');

                    // update the carrier block on the order detail
                    $carrierGroupDetail = json_decode($data['carrier_group_detail']);
                    
                    $carrierGroupDetail[0]->delivery_date = date('D, M d', strtotime($deliveryDate));
                    $carrierGroupDetail[0]->dispatch_date = date('D, M d', strtotime($dispatchDate));
                    
                    $this->shipperDetailResourceModel->getConnection()->update(
                        $this->shipperDetailResourceModel->getMainTable(),
                        ['carrier_group_detail' => json_encode($carrierGroupDetail)],
                        'order_id = ' . $order->getEntityId()
                    );
                    
                    // update detail record
                    $this->shipperDetailResourceModel->getConnection()->update(
                        $this->shipperDetailResourceModel->getMainTable(),
                        $changes,
                        'order_id = ' . $order->getEntityId()
                    );
                    
                    // update grid record
                    $this->shipperGridDetailResourceModel->getConnection()->update(
                        $this->shipperGridDetailResourceModel->getMainTable(),
                        $changes,
                        'order_id = ' . $order->getEntityId()
                    );
                    $this->extOrder->createNewExtSalesOrder($order->getEntityId(), array_keys($changes));

                    // insert entry to queue
                    $queueModel = $this->queueFactory->create();
                    $queueModel->addData([
                        "entity_id" => (int) $order->getId(),
                        "entity_type" => "order",
                        "created_at" => date("Y-m-d h:i:s")
                    ]);
                    $queueModel->save();

                    $this->addUpdateComment($order, $changes);
                }
            }
            $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
            return $resultRedirect;
        }
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
    
    protected function addUpdateComment($order, $changes) {
       $comment = "Delivery dates updated: ";
       
       if (isset($changes['dispatch_date']) === true) {
           $comment .= " shipping " . date("F j, Y", strtotime($changes['dispatch_date'])) . " ";
       }
       
       if (isset($changes['delivery_date']) === true) {
           $comment .= " estimated delivery on " . date("F j, Y", strtotime($changes['delivery_date']));
       }
       
       try {
           if ($order->canComment()) {
               $history = $this->orderHistoryFactory->create()
                   ->setEntityName(\Magento\Sales\Model\Order::ENTITY)
                   ->setComment(
                       __('%1.', $comment)
                   );

               $history->setIsCustomerNotified(false)
                       ->setIsVisibleOnFront(false);

               $order->addStatusHistory($history);
               $this->orderRepository->save($order);
           }
       } catch (NoSuchEntityException $exception) {
           $this->logger->error($exception->getMessage());
       }
    }
}
