<?php

    require_once $_SERVER['HOME'] . '/html/app/Mage.php';
    
    Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
	
	Class Rewards {
		
		public function __construct() {
			$this->processList();
		}
		
		protected function sendCreditEmail($name = null, $email = null, $pointsAmount = 0)
		{
			echo "Sending email to " . $email . "...\n";

			$templateId = 86;

			$creditAmount = number_format($pointsAmount * 0.01, 2);

			// Set sender information
			$senderName = Mage::getStoreConfig('trans_email/ident_support/name');
			$senderEmail = Mage::getStoreConfig('trans_email/ident_support/email');

			$sender = array(
				'name' => $senderName,
				'email' => $senderEmail
			);

			// Set variables that can be used in email template
			$vars = array(
				'name' => $name,
				'email' => $email,
				'points_value' => $pointsAmount,
				'credit_value' => $creditAmount
			);

			$translate  = Mage::getSingleton('core/translate');
			
			try {
				// Send Transactional Email
				Mage::getModel('core/email_template')->sendTransactional(
					$templateId,
					$sender,
					//$email,
					'paul.baum@forevercompanies.com',
					$name,
					$vars
				);

				$translate->setTranslateInline(true);
			}
			catch (Exception $e) {
				print($e->getMessage());
			}
		}
		
		protected function processList()
		{
			$file = fopen( Mage::getBaseDir('var') . '/import/points-email-source-2.csv', 'r');
			
			while (($line = fgetcsv($file)) !== FALSE) {
				
				$email = $line[0];
				$name = $line[1];
				$rewardPointsAmount = $line[2];
			  
				$this->sendCreditEmail(
					$name,
					$email,
					$rewardPointsAmount
				);
			}
			
			fclose($file);
		}
		
		

	}
	
	$rewards = new Rewards();
