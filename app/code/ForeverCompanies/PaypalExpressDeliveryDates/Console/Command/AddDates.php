<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\PaypalExpressDeliveryDates\Console\Command;

use ForeverCompanies\CustomSales\Cron\ExpirationDate;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use ForeverCompanies\CustomSales\Helper\Shipdate;

class AddDates extends Command
{
    protected $state;
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

    protected $name = 'forevercompanies:paypal-order-add-dates';

    /**
     * AddDates constructor.
     * @param State $state
     * @param OrderRepositoryInterface $orderRepositoryInterface
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        State $state,
        OrderRepositoryInterface $orderRepositoryInterface,
        ResourceConnection $resourceConnection,
        Shipdate $shipdateHelper
    ) {
        $this->state = $state;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->resourceConnection = $resourceConnection;
        $this->shipdateHelper = $shipdateHelper;

        parent::__construct($this->name);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_GLOBAL);

        # TODO: add a CLI input per magento docs
        $order = $this->orderRepositoryInterface->get(569997);

        $this->orderId = $order->getId();
        $this->connection = $this->resourceConnection->getConnection();
        $this->orderDetailTable = $this->connection->getTableName("shipperhq_order_detail");
        $this->orderGridDetailTable = $this->connection->getTableName("shipperhq_order_detail_grid");

        # get month
        $currentMonth = date("m", time());
        $currentYear = date("Y", time());

        if ($order->getPayment()->getMethod() == 'braintree_paypal') {
            $this->shippingDescription = $order->getShippingDescription();
            $this->shippingPrice = $order->getShippingAmount();
            $this->carrier = trim(substr($this->shippingDescription, 0, strpos($this->shippingDescription, "-")));

            # check for a delivery date in the text
            $deliveryDate = strrchr($this->shippingDescription, "Delivers: ");

            if ($deliveryDate !== false) {
                $dateString = substr($deliveryDate, 10);
                # orders that are placed in december may have a delivery date of january so we need to add the year
                $dateString .= " " . (($currentMonth == "12") ? $currentYear + 1 : $currentYear);
                # convert to int to be able to format for sql entries
                $dateInt = strtotime($dateString);

                if ($dateInt != 0) {
                    $this->deliveryTimestamp = $dateInt;
                    $this->dispatchTimestamp = $this->getDispatchDate($this->shippingDescription, $dateInt);

                    $orderDetail = $this->getOrderDetail();
                    $orderGridDetail = $this->getOrderGridDetail();

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
                }
            }
        }
    }

    protected  function getDispatchDate($shippingDescription = null, $deliveryDateTimestamp = 0) {
        $businessDays = 1;

        # determine the shipping service to back fill lead time
        if (strpos($shippingDescription, "Standard")) {
            $this->leadTime = 2;
        } elseif (strpos($shippingDescription, "Express")) {
            $this->leadTime = 1;
        } elseif (strpos($shippingDescription, "PO")) {
            $this->leadTime = 3;
        } else {
            # default to 4 day lead time
            $this->leadTime = 4;
        }

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
                dispatch_date = '" . $this->dispatchTimestamp . "',
                delivery_date = '" . $this->deliveryTimestamp . "';");
    }

    protected function updateOrderGridDetail()
    {
        $this->connection->query("UPDATE
                {$this->orderGridDetailTable}
            SET
                carrier_group = 'Forever Companies',
                dispatch_date = '" . $this->dispatchTimestamp . "',
                delivery_date = '" . $this->deliveryTimestamp . "'
            WHERE
                order_id = '" . (int) $this->orderId . "';");
    }

    protected function getOrderDetail($orderId = 0): array
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
