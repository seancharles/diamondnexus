<?php

 //   require_once $_SERVER['HOME'] . 'magento//Mage.php';
    
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
	Class Rewards {
		
		protected $readConnection;
		protected $pointsIndexList;
		protected $storeMap;
		
		public function __construct() {
			
			$this->readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
			
			$this->getStoreMap();
			$this->buildReport();
			$this->export();
		}
		
		protected function getStoreMap()
		{
			foreach (Mage::app()->getWebsites() as $website) {
				foreach ($website->getGroups() as $group) {
					$stores = $group->getStores();
					foreach ($stores as $store) {
						$this->storeMap[$store->getId()] = $website->getName() . " - " . $store->getName();
					}
				}
			}
		}
		
		protected function buildReport()
		{
            $pointsIndexQuery = "SELECT
								fn.value firstname,
								ln.value lastname,
								c.email,
								p.customer_id,
								p.customer_points_usable,
								'' as points,
								'' as comment,
								'' as effective_start,
								'' as store_id
							FROM
								rewards_customer_index_points p
							LEFT JOIN
								customer_entity c ON p.customer_id = c.entity_id
							LEFT JOIN
								customer_entity_varchar fn ON c.entity_id = fn.entity_id AND fn.attribute_id = 5
							LEFT JOIN
								customer_entity_varchar ln ON c.entity_id = ln.entity_id AND ln.attribute_id = 7
							WHERE
								p.customer_points_usable > 0
							AND
								fn.entity_type_id = 1
							AND
								ln.entity_type_id = 1
							ORDER BY
								c.entity_id DESC;";
            
            $this->pointsIndexList = $this->readConnection->fetchAll($pointsIndexQuery);
			
			foreach($this->pointsIndexList as &$pointIndex) {
				
				$customerQuery = "SELECT
										t.quantity as points,
										t.comments,
										t.effective_start,
										o.store_id
									FROM
										rewards_transfer t
									LEFT JOIN
										rewards_transfer_reference r ON t.rewards_transfer_id = r.rewards_transfer_id
									LEFT JOIN
										sales_flat_order o ON r.reference_id = o.entity_id
										
									WHERE
										t.customer_id = {$pointIndex[customer_id]}
									AND
										t.effective_start > '" . date("Y-m-d", strtotime("Now -1 year")) . " 00:00:00';";
							
				$customerPoints = $this->readConnection->fetchAll($customerQuery);
				
				$pointIndex['customer_points_transfers'] = $customerPoints;
				
			}
		}
		
		public function export()
		{
			$file = 'rewards-report-' . time() . '.csv';
			$path = Mage::getBaseDir('var') . DS . 'export' . DS . $file;
			$fields = [];

			$fp = fopen($path, 'w+');

			$rewardData = $this->pointsIndexList;

			foreach ($rewardData[0] as $key => $value) {
				if($key != 'customer_points_transfers') {
					$fields[] = $key;
				}
			}

			fputcsv($fp, $fields);
			
			// remove first row
			array_shift($rewardData);

			foreach ($rewardData as $row) {
				
				$formattedRow = [];
				$transfersList = [];
				
				// iterate column data
				foreach($row as $key => $value) {
					if($key == 'customer_points_transfers') {
						foreach($value as $transfer) {
							
							// map store_id to store name
							if(strlen($transfer['store_id']) > 0) {
								$transfer['store_id'] = $this->storeMap[$transfer['store_id']];
							}
							
							// add blank values to indent to match column heading
							array_unshift($transfer, '', '', '', '', '');
							
							$transfersList[] = $transfer;
						}
					} else {
						$formattedRow[] = $value;
					}
				}
				
				fputcsv($fp, $formattedRow);
				
				if(count($transfersList) >  0) {
					foreach($transfersList as $transferLine) {
						
						fputcsv($fp, $transferLine);
					}
				}
			}

			fclose($fp);

			echo "Report Completed!\n";
			echo "File: var/export/" . $file . "\n";
		}
	}
	
	$rewards = new Rewards();
