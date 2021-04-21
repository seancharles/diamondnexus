<?php
ini_set('display_errors', '1');
ini_set('memory_limit','-1');
//require_once $_SERVER['HOME'].'magento//Mage.php';
Mage::app();

// Get the current store id
$storeId = Mage::app()->getStore()->getId();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);

function help() {
	print "prune - remove stones older than 14 days\n";
	print "import override (0 or 1) index (0 or 1) - Run a stones import\n";
	print "remove <vendor name> - Remove a vendor and their stones\n";
	print "enable <vendor name> - start a vendor's import again\n";
	print "help - prints this menu\n";
}

switch ($argv[1]) {
	case 'prune':
		Mage::getModel('import/stonesintermediary')->pruneStonesDb();
		break;
	case 'import':
		Mage::getModel('import/stonesintermediary')->loadStonesDb($argv[2],$argv[3]);
		break;
	case 'remove':
		Mage::getModel('import/stonesintermediary')->pruneVendor($argv[2]);
		break;
	case 'enable':
		Mage::getModel('import/stonesintermediary')->enableVendor($argv[2]);
		break;
	default:
		help();
}
?>
