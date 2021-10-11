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

class AddDates extends Command
{
    protected $state;
    protected $orderRepositoryInterface;
    protected $resourceConnection;
    protected $connection;
    protected $orderDetailTable;
    protected $orderGridDetailTable;

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
        ResourceConnection $resourceConnection
    ) {
        $this->state = $state;
        $this->orderRepositoryInterface = $orderRepositoryInterface;
        $this->resourceConnection = $resourceConnection;

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
        $order = $this->orderRepositoryInterface->get(569997);

        $this->connection = $this->resourceConnection->getConnection();
        $orderDetailTable = $this->connection->getTableName("shipperhq_order_detail");
        $orderGridDetailTable = $this->connection->getTableName("shipperhq_order_detail_grid");

        # get month
        $currentMonth = date("m", time());
        $currentYear = date("Y", time());

        if ($order->getPayment()->getMethod() == 'braintree_paypal') {
            $shippingDescription = $order->getShippingDescription();

            # check for a delivery date in the text
            $deliveryDate = strrchr($shippingDescription, "Delivers: ");

            if ($deliveryDate !== false) {
                $dateString = substr($deliveryDate, 10);
                # orders that are placed in december may have a delivery date of january so we need to add the year
                $dateString .= " " . (($currentMonth == "12") ? $currentYear + 1 : $currentYear);
                # convert to int to be able to format for sql entries
                $dateInt = strtotime($dateString);

                $output->writeln($dateString);
                $output->writeln($dateInt);

                if ($dateInt != 0) {
                    $sqlDeliveryDate = date("Y-m-d", $dateInt);
                    $output->writeln($sqlDeliveryDate);

                    //print_r($this->getOrderDetail($order->getId()));
                    print_r($this->getOrderGridDetail($order->getId()));

                    $orderGridDetail = $this->getOrderGridDetail($order->getId());

                    if (isset($orderGridDetail[0]) === true) {
                        # update query
                    } else {
                        # insert row
                    }
                }
            }
        }
    }

    protected function getOrderGridDetail($orderId = 0): array
    {
        return $this->connection->fetchAll("SELECT id FROM {$this->orderGridDetailTable} WHERE order_id = '" . (int) $orderId . "'");
    }

    protected function insertOrderGridDetail($orderId = 0, $dispatchDate, $deliveryDate) {
        $this->connection->query("INSERT INTO
                {$this->orderGridDetailTable}
            SET
                order_id = '" . (int) $orderId . "',
                dispatch_date = '" . $dispatchDate . "',
                delivery_date = '" . $deliveryDate . "';");
    }

    protected function updateOrderGridDetail($orderId = 0, $dispatchDate, $deliveryDate) {
        $this->connection->query("UPDATE
                {$this->orderGridDetailTable}
            SET
                dispatch_date = '" . $dispatchDate . "',
                delivery_date = '" . $deliveryDate . "'
            WHERE
                order_id = '" . (int) $orderId . "';");
    }

    protected function getOrderDetail($orderId = 0): array
    {
        return $this->connection->fetchAll("SELECT id FROM {$this->orderDetailTable} WHERE order_id = '" . (int) $orderId . "'");
    }

    protected function insertOrderDetail($orderId = 0) {
        $this->connection->query("INSERT INTO {$this->orderGridDetailTable} SET delivery_date = '';");
    }

    protected function updateOrderDetail($orderId = 0) {
        $this->connection->query("UPDATE {$this->orderGridDetailTable} SET delivery_date = '' WHERE order_id = '" . (int) $orderId ."';");
    }
}
