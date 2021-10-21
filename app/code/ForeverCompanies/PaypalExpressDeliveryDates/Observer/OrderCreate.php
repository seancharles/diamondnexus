<?php

namespace ForeverCompanies\PaypalExpressDeliveryDates\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use ForeverCompanies\CustomSales\Helper\Shipdate;

class OrderCreate implements ObserverInterface
{
    protected $orderRepositoryInterface;
    protected $resourceConnection;
    protected $shipdateHelper;
    protected $connection;
    protected $orderDetailTable;
    protected $orderGridDetailTable;

    protected $orderId;
    protected $shippingDescription;
    protected $carrier;
    protected $leadTime;
    protected $dispatchTimestamp;
    protected $deliveryTimestamp;
    protected $shippingPrice;

    /**
     * AddDates constructor.
     * @param ResourceConnection $resourceConnection
     * @param Shipdate $shipdateHelper
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Shipdate $shipdateHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->shipdateHelper = $shipdateHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $this->orderId = $order->getId();
        $this->connection = $this->resourceConnection->getConnection();
        $this->orderDetailTable = $this->connection->getTableName("shipperhq_order_detail");
        $this->orderGridDetailTable = $this->connection->getTableName("shipperhq_order_detail_grid");

        if ($order->getPayment()->getMethod() == 'braintree_paypal') {
            $this->shippingDescription = $order->getShippingDescription();
            $this->setCarrierInfo($this->shippingDescription);
            $this->shippingPrice = $order->getShippingAmount();

            # check for a delivery date in the text
            $deliveryDate = strrchr($this->shippingDescription, "Delivers: ");

            if ($deliveryDate !== false) {
                $dateString = substr($deliveryDate, 10);
                # convert to int to be able to format for sql entries
                $dateInt = strtotime($dateString);

                if ($dateInt != 0) {
                    $this->deliveryTimestamp = $dateInt;
                    $this->dispatchTimestamp = $this->getDispatchDate($this->shippingDescription, $dateInt);

                    $orderDetail = $this->getOrderDetail();
                    $orderGridDetail = $this->getOrderGridDetail();

                    if (isset($orderDetail[0]) === true) {
                        if ($orderDetail[0]['dispatch_date'] == null) {
                            $this->updateOrderDetail();
                        }
                    } else {
                        $this->insertOrderDetail();
                    }

                    if (isset($orderGridDetail[0]) === true) {
                        if ($orderGridDetail[0]['dispatch_date'] == null) {
                            $this->updateOrderGridDetail();
                        }
                    } else {
                        $this->insertOrderGridDetail();
                    }
                }
            }
        }
    }

    protected function setCarrierInfo($shippingDescription = null) {
        # determine the shipping service to back fill lead time
        if (strpos($shippingDescription, "Standard Shipping") !== false) {
            $this->leadTime = 2;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Standard Saturday") !== false) {
            $this->leadTime = 2;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Express Shipping") !== false) {
            $this->leadTime = 1;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Express Saturday") !== false) {
            $this->leadTime = 1;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Ground") !== false) {
            $this->leadTime = 3;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Express Worldwide") !== false) {
            $this->leadTime = 3;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "Expedited Worldwide") !== false) {
            $this->leadTime = 3;
            $this->carrier = 'Fedex';
        } elseif (strpos($shippingDescription, "USPS PO Boxes") !== false) {
            $this->leadTime = 3;
            $this->carrier = 'Fedex';
        } else {
            # default to 3 day lead time
            $this->carrier = 'flatrate';
            $this->leadTime = 3;
        }
    }

    protected  function getDispatchDate($shippingDescription = null, $deliveryDateTimestamp = 0) {
        $businessDays = 1;

        for($i=1; $i<=10; $i++) {
            # go backward x days from the delivery date
            $newTimestamp = $deliveryDateTimestamp - ($i * 86400);

            if ($this->shipdateHelper->isBusinessDay($newTimestamp) === true) {
                if ($businessDays == $this->leadTime) {
                    return $newTimestamp;
                }
                $businessDays++;
            }
        }
    }

    protected function getOrderGridDetail(): array
    {
        return $this->connection->fetchAll("SELECT id, dispatch_date, delivery_date FROM {$this->orderGridDetailTable} WHERE order_id = '" . (int) $this->orderId . "';");
    }

    protected function insertOrderGridDetail() {
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
        return $this->connection->fetchAll("SELECT id, dispatch_date, delivery_date FROM {$this->orderDetailTable} WHERE order_id = '" . (int) $this->orderId . "';");
    }

    protected function insertOrderDetail() {
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
                    "carrierTitle":"' . $this->carrier . '",
                    "carrierName":"' . $this->carrier . '",
                    "methodTitle":"' . $this->leadTime . ' day",
                    "price":"' . $this->shippingPrice . '",
                    "hideNotifications":false,
                    "code":"flatrate_flatrate",
                    "delivery_date":"' . $this->getTextFormattedDate($this->deliveryTimestamp) . '",
                    "dispatch_date":"' . $this->getTextFormattedDate($this->dispatchTimestamp) . '"
                }]' . "';");
    }

    protected function updateOrderDetail() {
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
                    "carrierTitle":"' . $this->carrier . '",
                    "carrierName":"' . $this->carrier . '",
                    "methodTitle":"' . $this->leadTime . ' day",
                    "price":"' . $this->shippingPrice . '",
                    "hideNotifications":false,
                    "code":"flatrate_flatrate",
                    "delivery_date":"' . $this->getTextFormattedDate($this->deliveryTimestamp) . '",
                    "dispatch_date":"' . $this->getTextFormattedDate($this->dispatchTimestamp) . '"
                }]' . "'
            WHERE
                order_id = '" . (int) $this->orderId . "';");
    }

    protected function getFormattedDate ($time = 0) {
        return date("Y-m-d", $time);
    }

    protected function getTextFormattedDate ($time = 0) {
        return date("D, M d", $time);
    }
}
