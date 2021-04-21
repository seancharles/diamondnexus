<?php

    require_once $_SERVER['HOME'] . '/html/app/Mage.php';
    
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
	Class Monitor {

		CONST LOG_FILE = 'fc-monitor.log';
		CONST ALERT_LOG_FILE = 'fc-alert.log';
		
		CONST ORDER_KEY = 'order';
		CONST ADD_TO_CART_KEY = 'atc';
		
		CONST ORDER_ENTITY = 'sales/order';
		CONST QUOTE_ENTITY = 'sales/quote';
		
		CONST SUNDAY = 'Sun';
		CONST MONDAY = 'Mon';
		CONST TUESDAY = 'Tue';
		CONST WEDNESDAY = 'Wed';
		CONST THURSDAY = 'Thu';
		CONST FRIDAY = 'Fri';
		CONST SATURDAY = 'Sat';
		
		// notification email address
		private $_notificationEmail = 'sms@diamondnexus.com';
		
		// stores the array of notifications to push via txt
		private $_noficiations = array();
		
		// stores the entity frequency threshold table
		public $_alertThreshold = array();
		
		public function __construct($debug = false) {
			
			$this->debug = $debug;
			
			if ($this->debug) {
				$this->_notificationEmail = 'paul.baum@forevercompanies.com';
			}
		}
		
		public function getLastModified($file = null) {
			
			// full path to log file
			$filename = $_SERVER['HOME'] . "/html/var/log/" . $file;
			
			if (file_exists($filename)) {
				return time() - filemtime($filename);
			} else {
				return -1;
			}
		}
		
		public function resetMapping() {
			$this->_alertThreshold = array();
		}
		
		public function setTfMapping() {
			
			// clear any existing mappings
			$this->resetMapping();
			
			// ATC
			$this->setAlertThreshold(
				array( self::SUNDAY, self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY ),
				0, 23, self::ADD_TO_CART_KEY, '8 hours'
			);
			
			// ORDERS
			$this->setAlertThreshold(
				array( self::SUNDAY, self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY ),
				0, 23, self::ORDER_KEY, '24 hours'
			);
		}
		
		public function setDnMapping() {

			// clear any existing mappings
			$this->resetMapping();
			
			// ATC
			$this->setAlertThreshold(self::SUNDAY,	7,	23,	self::ADD_TO_CART_KEY, '60 minutes');
			
			$this->setAlertThreshold(self::MONDAY,	7,	12,	self::ADD_TO_CART_KEY, '60 minutes');
			$this->setAlertThreshold(self::MONDAY,	13,	23,	self::ADD_TO_CART_KEY, '30 minutes');
			
			$this->setAlertThreshold(array(self::TUESDAY,self::WEDNESDAY,self::THURSDAY,self::FRIDAY,self::SATURDAY),	7,	11,	self::ADD_TO_CART_KEY, '60 minutes');
			$this->setAlertThreshold(array(self::TUESDAY,self::WEDNESDAY,self::THURSDAY,self::FRIDAY,self::SATURDAY),	12,	23,	self::ADD_TO_CART_KEY, '30 minutes');
			
			// ORDERS
			$this->setAlertThreshold(self::SUNDAY, 7, 10, self::ORDER_KEY, '240 minutes');
			$this->setAlertThreshold(self::SUNDAY, 11, 21, self::ORDER_KEY, '120 minutes');
			$this->setAlertThreshold(self::SUNDAY, 22, 23, self::ORDER_KEY, '240 minutes');
			
			$this->setAlertThreshold(self::MONDAY, 7, 7, self::ORDER_KEY, '240 minutes');
			$this->setAlertThreshold(self::MONDAY, 8, 23, self::ORDER_KEY, '120 minutes');
			
			$this->setAlertThreshold(array(self::TUESDAY,self::WEDNESDAY,self::THURSDAY), 7, 7, self::ORDER_KEY, '240 minutes');
			$this->setAlertThreshold(array(self::TUESDAY,self::WEDNESDAY,self::THURSDAY), 8, 21, self::ORDER_KEY, '120 minutes');
			$this->setAlertThreshold(array(self::TUESDAY,self::WEDNESDAY,self::THURSDAY), 22, 23, self::ORDER_KEY, '240 minutes');
			
			$this->setAlertThreshold(self::FRIDAY, 7, 16, self::ORDER_KEY, '120 minutes');
			$this->setAlertThreshold(self::FRIDAY, 17, 23, self::ORDER_KEY, '240 minutes');
			
			$this->setAlertThreshold(self::SATURDAY, 7, 8, self::ORDER_KEY, '240 minutes');
			$this->setAlertThreshold(self::SATURDAY, 9, 20, self::ORDER_KEY, '180 minutes');
			$this->setAlertThreshold(self::SATURDAY, 21, 23, self::ORDER_KEY, '240 minutes');
		}
		
		public function setAlertThreshold($daysOfWeek = null, $startHour, $endHour, $type, $value) {
			
			if (is_array($daysOfWeek) == false) {
				$daysOfWeek = array(
					$daysOfWeek
				);
			}
			
			foreach($daysOfWeek as $day) {
				for($i=$startHour; $i<=$endHour; $i++) {
					$this->_alertThreshold[$day][$i][$type] = $value;
				}
			}
		}
		
		public function getEntityCount($modelType = null, $storeId = 0, $seconds = 0) {
			
			if($this->debug)
				echo "seconds = " . $seconds ."\n";
			
			$time = time() - $seconds;
			
			// pull the number of orders in the last hour
			$entityCollection = Mage::getModel($modelType)
							  ->getCollection()
							  ->addAttributeToSelect('entity_id')
							  ->addAttributeToFilter('store_id', $storeId)
							  ->addAttributeToFilter('created_at', array('gt' => date('Y-m-d H:i:s', $time)));
			
			if($this->debug)
				echo $entityCollection->getSelect() ."\n";
			
			return $entityCollection->getSize();
		}
		
		public function getFrequencyInterval($typeId = null) {
			
			// Mapping ranges were set in central time so the hour needs to be adjusted for CT
			date_default_timezone_set('America/Chicago'); 
			
			$dayOfWeek = date('D');
			$hourofDay = date('G');
			
			// reset to UTC
			date_default_timezone_set('UTC'); 
			
			if( $this->_alertThreshold[$dayOfWeek][$hourofDay][$typeId] ) {
				return strtotime('now +' . $this->_alertThreshold[$dayOfWeek][$hourofDay][$typeId]) - time();
			} else {
				return -1;
			}
		}
		
		public function getFrequencyIntervalText($typeId = null) {
			
			// Mapping ranges were set in central time so the hour needs to be adjusted for CT
			date_default_timezone_set('America/Chicago'); 
			
			$dayOfWeek = date('D');
			$hourofDay = date('G');
			
			// reset to UTC
			date_default_timezone_set('UTC'); 
			
			return $this->_alertThreshold[$dayOfWeek][$hourofDay][$typeId];
		}
		
		public function getAlerts($brand) {
			
			$storeId = 0;
			
			if ($brand == 'DN') {
				$this->setDnMapping();
				$storeId = 1;
			} elseif( $brand == 'TF') {
				$this->setTfMapping();
				$storeId = 12;
			}
			
			$orderInterval = $this->getFrequencyInterval(self::ORDER_KEY);
			$quoteInterval = $this->getFrequencyInterval(self::ADD_TO_CART_KEY);
			
			if ($orderInterval != -1) {
				
				$orderCount = $this->getEntityCount(self::ORDER_ENTITY, $storeId, $orderInterval);
				$orderText = $this->getFrequencyIntervalText(self::ORDER_KEY);
				
				Mage::log("Orders = " . $orderCount . " " . date('c') , null, self::LOG_FILE);
				
				if ($orderCount == 0) {
					
					$alertText = 'No ' . $brand . ' orders placed in ' . $orderText;
					
					Mage::log($alertText . ' ' . date('c') , null, self::LOG_FILE);
					
					$this->_noficiations[] = $alertText;
				}
			}
			
			if ($quoteInterval != -1) {
				
				$quoteCount = $this->getEntityCount(self::QUOTE_ENTITY, $storeId, $quoteInterval);
				$quoteText = $this->getFrequencyIntervalText(self::ADD_TO_CART_KEY);
				
				Mage::log("Quotes = " . $quoteCount . " " . date('c') , null, self::LOG_FILE);
				
				if ($quoteCount == 0) {
					
					$alertText = 'No ' . $brand . ' ATC in ' . $quoteText;
					
					Mage::log($alertText . ' ' . date('c') , null, self::LOG_FILE);
					
					$this->_noficiations[] = $alertText;
				}
			}
		}
		
		public function getSalesforceAlerts() {
			
			// path to log files
			$logPath = $_SERVER['HOME'] . "/html/var/log/";
			
			// get salesforce log filename
			$sfLogFile = HN_Salesforce_Model_Connector::LOG_FILE;
			
			// get last modified time difference
			$lastModified = $this->getLastModified($sfLogFile);
			
			// check if the salesforce log file has been updated in the last 10 minutes
			if ($lastModified != -1 && $lastModified < 3600) {
				
				// get the last line from the log file
				$lastline = system("tail -n 1 " . $logPath . $sfLogFile );

				
				$this->_noficiations[] = 'Salesforce Error: ' . $lastline;
			}
		}
		
		public function canSendAlerts() {
			
			// Mapping ranges were set in central time so the hour needs to be adjusted for CT
			date_default_timezone_set('America/Chicago'); 
			
			$hour = date('G');
			
			// reset to UTC
			date_default_timezone_set('UTC'); 
			
			if( $hour <= 6 ) {
				return false;
			}
			
			return true;
		}
		
		public function sendAlerts() {
			
			// pulls alert text for stores
			$this->getAlerts('DN');
			$this->getAlerts('TF');
			
			// pull any issues from SF log
			$this->getSalesforceAlerts();
			
			if (count($this->_noficiations) > 0) {
				
				// only alerts after 6am till midnight
				if ($this->canSendAlerts() == true) {
					// allow alerts to only send once an hour
					if ($this->getLastModified(self::ALERT_LOG_FILE) > 3600 || $this->getLastModified(self::ALERT_LOG_FILE) == -1) {
						// send alert
						mail($this->_notificationEmail, 'FC Site Monitor', implode("\n", $this->_noficiations));

						echo "Alert email sent.";
					}
				}
				
				Mage::log(print_r($this->_noficiations, true) . ' ' . date('c') , null, self::ALERT_LOG_FILE);
			}
		}
	}
	
	// debug command line param
	$debug = $argv[1];
	
	$fcMonitor = new Monitor($debug);
	$fcMonitor->sendAlerts();
