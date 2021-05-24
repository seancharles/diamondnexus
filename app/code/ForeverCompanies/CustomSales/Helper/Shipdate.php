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
use Magento\Framework\App\Config\ScopeConfigInterface;

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

    const XML_PATH_BLACKOUT_SHIPDATES = 'forevercompanies_customsales/shipping/blackout_dates';

    /**
     * SalesPerson constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;

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

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_CustomSales::shipdate_config');
    }
}
