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
use Magento\Framework\Serialize\Serializer\Json;

use ShipperHQ\Shipper\Model\ResourceModel\Order\Detail;
use ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail;

class Order extends AdminOrder implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'Magento_Sales::hold';
    protected $shipperResourceModel;
    protected $orderHistoryFactory;
    protected $extOrder;
    protected $queueFactory;
    protected $jsonEncoder;
    protected $shipperDetailResourceModel;
    protected $shipperGridDetailResourceModel;
    protected $connection;
    protected $orderDetailTable;
    protected $orderGridDetailTable;
    protected $dispatchTimestamp;
    protected $deliveryTimestamp;
    protected $shippingPrice;
    protected $shippingMethod;
    protected $carrierGroupDetail = null;
    protected $shippingCarrierCode;
    protected $shippingMethodCode;
    protected $orderId;

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
        QueueFactory  $queueFactory,
        Json $json
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
        $this->jsonEncoder = $json;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute()
    {
        $changes = [];

        $resultRedirect = $this->resultRedirectFactory->create();

        $order = $this->_initOrder();
        if ($order) {
            /** @var Http $http */
            $http = $this->getRequest();
            $post = $http->getPostValue();
            $dispatchDate = filter_var($post['dispatch_date'], FILTER_SANITIZE_SPECIAL_CHARS);
            $deliveryDate = filter_var($post['delivery_date'], FILTER_SANITIZE_SPECIAL_CHARS);
            $this->dispatchTimestamp = strtotime($dispatchDate);
            $this->deliveryTimestamp = strtotime($deliveryDate);
            if (strtotime($dispatchDate) > strtotime($deliveryDate)) {
                $this->messageManager->addErrorMessage('Dispatch date must be before Delivery date!');
            } elseif (strtotime($dispatchDate) == 0 && strtotime($deliveryDate) == 0) {
                $this->messageManager->addErrorMessage('Delivery dates must be specified!');
            } else {
                $this->connection = $this->shipperDetailResourceModel->getConnection();
                $this->orderDetailTable = $this->connection->getTableName("shipperhq_order_detail");
                $this->orderGridDetailTable = $this->connection->getTableName("shipperhq_order_detail_grid");
                $this->orderId = $order->getId();
                $this->shippingPrice = $order->getShippingAmount();
                $this->shippingMethod = $order->getShippingMethod();
                $aMethodData = explode("_", $this->shippingMethod);

                $this->shippingCarrierCode = $aMethodData[0];
                $this->shippingMethodCode = $aMethodData[1];

                $orderDetail = $this->getOrderDetail();
                $orderGridDetail = $this->getOrderGridDetail();

                if (isset($orderDetail[0]) === true) {
                    if (isset($orderDetail[0]['carrier_group_detail']) === true) {
                        $this->carrierGroupDetail = $this->jsonEncoder->unserialize($orderDetail[0]['carrier_group_detail']);
                    }
                    if ($orderDetail[0]['dispatch_date'] != $dispatchDate) {
                        $changes['dispatch_date'] = $dispatchDate;
                    }
                    if ($orderDetail[0]['delivery_date'] != $deliveryDate) {
                        $changes['delivery_date'] = $deliveryDate;
                    }
                } else {
                    $changes['dispatch_date'] = $dispatchDate;
                    $changes['delivery_date'] = $deliveryDate;
                }

                if (count($changes) > 0) {

                    $this->messageManager->addSuccess('Delivery dates have been updated!');

                    if (isset($orderDetail[0]) === true) {
                        $this->updateOrderDetail();
                    } else {
                        $this->insertOrderDetail();
                    }

                    if (isset($orderGridDetail[0]) === true) {
                        $this->updateOrderGridDetail();
                    } else {
                        $this->insertOrderGridDetail();
                    }

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
    
    protected function addUpdateComment($order, $changes)
    {
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

    protected function getOrderGridDetail(): array
    {
        return $this->connection->fetchAll("SELECT id, dispatch_date, delivery_date FROM {$this->orderGridDetailTable} WHERE order_id = '" . (int) $this->orderId . "';");
    }

    protected function insertOrderGridDetail()
    {
        $this->connection->query("INSERT INTO
                {$this->orderGridDetailTable}
            SET
                order_id = '" . (int) $this->orderId . "',
                carrier_group = 'Forever Companies',
                dispatch_date = '" . $this->getFormattedDate($this->dispatchTimestamp) . "',
                delivery_date = '" . $this->getFormattedDate($this->deliveryTimestamp) . "';");
    }

    protected function updateOrderGridDetail()
    {
        $this->connection->query("UPDATE
                {$this->orderGridDetailTable}
            SET
                carrier_group = 'Forever Companies',
                dispatch_date = '" . $this->getFormattedDate($this->dispatchTimestamp) . "',
                delivery_date = '" . $this->getFormattedDate($this->deliveryTimestamp) . "'
            WHERE
                order_id = '" . (int) $this->orderId . "';");
    }

    protected function getOrderDetail(): array
    {
        return $this->connection->fetchAll("SELECT id, dispatch_date, delivery_date, carrier_group_detail FROM {$this->orderDetailTable} WHERE order_id = '" . (int) $this->orderId . "';");
    }

    protected function getCarrierGroupData($field = null, $default = null)
    {
        if (isset($this->carrierGroupDetail[$field]) === true) {
            return $this->carrierGroupDetail[$field];
        } else {
            return $default;
        }
    }

    protected function insertOrderDetail()
    {
        $this->connection->query("INSERT INTO
                {$this->orderDetailTable}
            SET
                order_id = '" . (int) $this->orderId . "',
                dispatch_date = '" . $this->getFormattedDate($this->dispatchTimestamp) . "',
                delivery_date = '" . $this->getFormattedDate($this->deliveryTimestamp) . "',
                carrier_group_detail = '" . '[{
                    "checkoutDescription":"Forever Companies",
                    "name":"Forever Companies",
                    "locale":"en-US",
                    "timezone":"America/Chicago",
                    "carrierTitle":"' . $this->getCarrierGroupData('carrierTitle', $this->shippingCarrierCode) . '",
                    "carrierName":"' . $this->getCarrierGroupData('carrierName', $this->shippingCarrierCode) . '",
                    "methodTitle":"' . $this->getCarrierGroupData('methodTitle',$this->shippingMethodCode) . '",
                    "price":"' . $this->shippingPrice . '",
                    "hideNotifications":false,
                    "code":"' . $this->getCarrierGroupData('code', $this->shippingMethod) . '",
                    "delivery_date":"' . $this->getTextFormattedDate($this->deliveryTimestamp) . '",
                    "dispatch_date":"' . $this->getTextFormattedDate($this->dispatchTimestamp) . '"
                }]' . "';");
    }

    protected function updateOrderDetail()
    {
        $this->connection->query("UPDATE
                {$this->orderDetailTable}
            SET
                dispatch_date = '" . $this->getFormattedDate($this->dispatchTimestamp) . "',
                delivery_date = '" . $this->getFormattedDate($this->deliveryTimestamp) . "',
                carrier_group_detail = '" . '[{
                    "checkoutDescription":"Forever Companies",
                    "name":"Forever Companies",
                    "locale":"en-US",
                    "timezone":"America/Chicago",
                    "carrierTitle":"' . $this->getCarrierGroupData('carrierTitle', 'flatrate') . '",
                    "carrierName":"' . $this->getCarrierGroupData('carrierName', 'flatrate') . '",
                    "methodTitle":"' . $this->getCarrierGroupData('methodTitle','flatrate') . '",
                    "price":"' . $this->shippingPrice . '",
                    "hideNotifications":false,
                    "code":"' . $this->getCarrierGroupData('code', 'flatrate_flatrate') . '",
                    "delivery_date":"' . $this->getTextFormattedDate($this->deliveryTimestamp) . '",
                    "dispatch_date":"' . $this->getTextFormattedDate($this->dispatchTimestamp) . '"
                }]' . "'
            WHERE
                order_id = '" . (int) $this->orderId . "';");
    }

    protected function getFormattedDate($time = 0)
    {
        return date("Y-m-d", $time);
    }

    protected function getTextFormattedDate($time = 0)
    {
        return date("D, M d", $time);
    }
}
