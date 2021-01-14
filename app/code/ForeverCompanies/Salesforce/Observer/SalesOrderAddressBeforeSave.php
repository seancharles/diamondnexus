<?php

    namespace ForeverCompanies\Salesforce\Observer;
	
    use Magento\Framework\Event\Observer as EventObserver;
    use Magento\Framework\Event\ObserverInterface;
    
	class SalesOrderAddressBeforeSave implements ObserverInterface
	{
		public function __construct(
			\Magento\Framework\Stdlib\DateTime\DateTime $date,
			\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
		)
		{
			$this->date = $date;
			$this->timezone = $timezone;
		}
		
		public function execute(\Magento\Framework\Event\Observer $observer)
		{
			$orderAddress = $observer->getEvent()->getAddress();
			$orderAddress->setData('address_updated_at', $this->date->gmtDate());
		}
	}
