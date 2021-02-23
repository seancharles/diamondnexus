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

class Shipdate extends AbstractHelper
{
	protected $_holidays;
	
    /**
     * SalesPerson constructor.
     * @param Context $context
     * @param User $userResource
     * @param Session $session
     */
    public function __construct(
		Context $context
	)
    {
        parent::__construct($context);
		
		$this->_holidays = [
			'7-4-2021',
			'12-24-2021',
			'12-25-2021',
			'12-31-2021'
		];
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
        
		if($timestamp) {
			$localTimestamp = $timestamp;
		} else {
			# the current timestamp
			$localTimestamp = time();
		}
		
		# set the cut-off time to 2PM
		$cutOff = mktime(14, 0, 0, (int) date('n', $localTimestamp), (int) date('j', $localTimestamp), (int) date('Y', $localTimestamp) );
        
        if( $this->isBusinessDay($localTimestamp) ) {
            
            # check before cutoff time
            if( $localTimestamp > $cutOff ) {
                $businessDays += 1;
            }   
        }
        
        while ( $dayCount <= $businessDays ) {
            
            $checkStamp = $this->adjustTimestampDays($localTimestamp, $i);
            
            # check if this is a business day
            if( $this->isBusinessDay($checkStamp) == true )
            {
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
     * @return boolean
     */
	public function isHoliday($time = 0)
	{
		# the text representation of the date
		$checkDate = date('Y-m-d', $time);

		foreach ($this->_holidays as $holiday)
		{
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
		switch( date('N', $time) )
		{
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
     * @return boolean
     */
	public function isBusinessDay($time = 0)
	{
		if( $this->isHoliday($time) == true )
		{
			return false;
		}

                if( $this->isWeekday($time) == false )
                {
                        return false;
                }

		return true;
	}
	
	public function getTimestamp($afterCutoff = false)
	{
		$time = time();
		
		if( $afterCutoff == true ){
			$timestamp = mktime(14, 0, 1, (int) date('n', $time), (int) date('j', $time), (int) date('Y', $time) );
		} else {
			$timestamp = mktime(0, 0, 0, (int) date('n', $time), (int) date('j', $time), (int) date('Y', $time) );
		}
		
		return $timestamp;
	}
	
    /**
     * Adjust the time stamp by number of days
     *
     * @return integer
     */
	public function adjustTimestampDays($time = 0, $days = 0)
	{
		return $time + ($days * 86400);
	}

    /**
     * @return bool
     */
    public function isAllowed()
    {
        return $this->session->isAllowed('ForeverCompanies_CustomSales::shipdate_config');
    }
}
