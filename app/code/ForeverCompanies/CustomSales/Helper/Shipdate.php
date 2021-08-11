<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ForeverCompanies\CustomSales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use ShipperHQ\Shipper\Model\ResourceModel\Order\Detail;
use ShipperHQ\Shipper\Model\ResourceModel\Order\GridDetail;

class Shipdate extends AbstractHelper
{
    /**
     * @var array|false|string[]
     */
    protected $holidays = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Detail
     */
    protected $shipperDetailResourceModel;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var HistoryFactory
     */
    protected $orderHistoryFactory;
    
    /**
     * @var GridDetail
     */
    protected $shipperGridDetailResourceModel;

    const XML_PATH_BLACKOUT_SHIPDATES = 'forevercompanies_customsales/shipping/blackout_dates';

    /**
     * SalesPerson constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Detail $shipperDetailResourceModel
     * @param GridDetail $shipperGridDetailResourceModel
     * @param OrderRepositoryInterface $orderRepository
     * @param HistoryFactory $orderHistoryFactory
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Detail $shipperDetailResourceModel,
        GridDetail $shipperGridDetailResourceModel,
        OrderRepositoryInterface $orderRepository,
        HistoryFactory $orderHistoryFactory,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shipperDetailResourceModel = $shipperDetailResourceModel;
        $this->shipperGridDetailResourceModel = $shipperGridDetailResourceModel;
        $this->orderRepository = $orderRepository;
        $this->orderHistoryFactory = $orderHistoryFactory;

        parent::__construct($context);

        $blackoutDates = $this->scopeConfig->getValue(
            self::XML_PATH_BLACKOUT_SHIPDATES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (isset($blackoutDates) == true) {
            // parse blackout dates comma separated list
            $this->holidays = explode(",", $blackoutDates);
        }
    }

    /**
     * Function for estimating the ASD. The ASD cannot be a day on the weekend
     * and will automatically add one business day if the orders placed during
     * the week after the cut-off time.
     *
     * @param int $businessDays - The number of business days to deliver for an item in the order
     * @param boolean $timestamp
     * @return string
     */
    public function getShipdate($businessDays, $timestamp = false)
    {
        $i = 0;
        $dayCount = 0;
        $lastDay = 0;

        if ($timestamp) {
            $localTimestamp = $timestamp;
        } else {
            # the current timestamp
            $localTimestamp = time();
        }

        # set the cut-off time to 2PM
        $month = (int)date('n', $localTimestamp);
        $day = (int)date('j', $localTimestamp);
        $year = (int)date('Y', $localTimestamp);
        $cutOff = mktime(14, 0, 0, $month, $day, $year);

        if ($this->isBusinessDay($localTimestamp)) {
            # check before cutoff time
            if ($localTimestamp > $cutOff) {
                $businessDays++;
            }
        }

        while ($dayCount <= $businessDays) {
            $checkStamp = $this->adjustTimestampDays($localTimestamp, $i);

            # check if this is a business day
            if ($this->isBusinessDay($checkStamp) == true) {
                $dayCount++;

                $lastDay = $checkStamp;
            }

            $i++;
        }

        return date('Y-m-d', $lastDay);
    }

    /**
     * Determine if the day is a holiday
     *
     * @param int $time
     * @return boolean
     */
    public function isHoliday($time = 0)
    {
        # the text representation of the date
        $checkDate = date('Y-m-d', $time);

        foreach ($this->holidays as $holiday) {
            if ($checkDate == $holiday) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the day is a weekday
     *
     * @return boolean
     */
    public function isWeekday($time = 0)
    {
        switch (date('N', $time)) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return true;
            default:
                return false;
        }
    }

    /**
     * Determine if the day is business day
     *
     * @param int $time
     * @return boolean
     */
    public function isBusinessDay($time = 0)
    {
        if ($this->isHoliday($time) == true) {
            return false;
        }

        if ($this->isWeekday($time) == false) {
                return false;
        }

        return true;
    }

    /**
     * @param bool $afterCutoff
     * @return bool|int
     */
    public function getTimestamp($afterCutoff = false)
    {
        $time = time();

        if ($afterCutoff == true) {
            $timestamp = mktime(14, 0, 1, (int) date('n', $time), (int) date('j', $time), (int) date('Y', $time));
        } else {
            $timestamp = mktime(0, 0, 0, (int) date('n', $time), (int) date('j', $time), (int) date('Y', $time));
        }

        return $timestamp;
    }

    public function adjustDeliveryDate($date = null, $days = 0)
    {
        $time = strtotime($date);
        $i = 0;
        $businessDays = 0;
        
        while($businessDays < $days) {
            $newTimestamp = $this->adjustTimestampDays($time, $i);
            
            if($this->isBusinessDay($newTimestamp) === true) {
                $businessDays++;
            }
            
            $i++;
        }
        
        $date = date('y-m-d', $this->adjustTimestampDays($time, $businessDays));
        
        return $date;
    }

    /**
     * Adjust the time stamp by number of days
     *
     * @param int $time
     * @param int $days
     * @return integer
     */
    public function adjustTimestampDays($time = 0, $days = 0)
    {
        return $time + ($days * 86400);
    }
    
    public function getDateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
    {
        $datetime1 = date_create($date_1);
        $datetime2 = date_create($date_2);
       
        $interval = date_diff($datetime1, $datetime2);
       
        return $interval->format($differenceFormat);
    }
    
    public function getDeliveryDates($order)
    {
        $deliveryDates = [
            'dispatch_date' => null,
            'delivery_date' => null
        ];
        
        $connection = $this->shipperDetailResourceModel->getConnection();
        $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
            ->where('order_id = ?', $order->getEntityId())
            ->order('id desc')
            ->limit(1);
        
        // pull the existing order delivery dates
        $data = $connection->fetchRow($select);
        
        if(isset($deliveryDates['dispatch_date']) === true) {
            $deliveryDates[''] = $data['dispatch_date'];
        }
        
        if(isset($deliveryDates['delivery_date']) === true) {
            $deliveryDates = $data['delivery_date'];
        }
        
        return $deliveryDates;
    }
    
    public function updateDeliveryDates($order)
    {
        // New orders don't need updated delivery dates
        if(!$order->getEntityId()) {
            return;
        }
        
        $connection = $this->shipperDetailResourceModel->getConnection();
        $select = $connection->select()->from($this->shipperDetailResourceModel->getMainTable())
            ->where('order_id = ?', $order->getEntityId())
            ->order('id desc')
            ->limit(1);
        
        // pull the existing order delivery dates
        $data = $connection->fetchRow($select);
        
        // get the number of days since the order was created
        $daysAfterCreate = $this->getDateDifference( $order->getCreatedAt(), date('Y-m-d') );
        
        if($daysAfterCreate == 0 || isset($data['dispatch_date']) === false || isset($data['delivery_date']) === false) {
            return;
        }
        
        $dispatchDate =  $data['dispatch_date'];
        $deliveryDate = $data['delivery_date'];
        
        // calculate the new dates by adding x number of business days since the order was created
        $newDispatchDate = $this->adjustDeliveryDate($dispatchDate, $daysAfterCreate);
        $newDeliveryDate = $this->adjustDeliveryDate($deliveryDate, $daysAfterCreate);
        
        $deliveryDates = [
            'dispatch_date' => $newDispatchDate,
            'delivery_date' => $newDeliveryDate
        ];
        
        $this->addUpdateComment($order, $deliveryDates);
        
        // update the carrier block on the order detail
        $carrierGroupDetail = json_decode($data['carrier_group_detail']);
        
        $carrierGroupDetail[0]->dispatch_date = date('D, M d', strtotime($newDispatchDate));
        $carrierGroupDetail[0]->delivery_date = date('D, M d', strtotime($newDeliveryDate));
        
        $this->shipperDetailResourceModel->getConnection()->update(
            $this->shipperDetailResourceModel->getMainTable(),
            ['carrier_group_detail' => json_encode($carrierGroupDetail)],
            'order_id = ' . $order->getEntityId()
        );
        
        // update detail record
        $this->shipperDetailResourceModel->getConnection()->update(
            $this->shipperDetailResourceModel->getMainTable(),
            $deliveryDates,
            'order_id = ' . $order->getEntityId()
        );
        
        // update grid record
        $this->shipperGridDetailResourceModel->getConnection()->update(
            $this->shipperGridDetailResourceModel->getMainTable(),
            $deliveryDates,
            'order_id = ' . $order->getEntityId()
        );
    }
    
    public function getTrackingInfo($order)
    {
        $tracksCollection = $order->getTracksCollection();
        $trackingPath = false;

        $result = [
            'tracking_provider' => null,
            'tracking_number' => null,
            'tracking_url' => null
        ];

        foreach ($tracksCollection->getItems() as $track) {
            switch($track->getTitle()) {
                case "Federal Express":
                    $trackingPath = "https://www.fedex.com/Tracking?tracknumbers=";
                    break;
                case "United Postal Service":
                    $trackingPath = "https://tools.usps.com/go/TrackConfirmAction?tLabels=";
                    break;
                case "United Parcel Service":
                    $trackingPath = "https://wwwapps.ups.com/tracking/tracking.cgi?tracknum=";
                    break;
            }
            
            if($trackingPath) {
                $result['tracking_provider'] = $track->getTitle();
                $result['tracking_number'] = $track->getTrackNumber();
                $result['tracking_url'] = $trackingPath . $track->getTrackNumber();
            }
            
            // only return for the first shipment
            break;
        }
        
        return $result;
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

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_CustomSales::shipdate_config');
    }
}
