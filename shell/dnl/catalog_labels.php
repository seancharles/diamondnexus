<?php
//ini_set('display_errors', '1');
require_once $_SERVER['HOME'].'/html/app/Mage.php';
Mage::app();
// Get the current store id
$storeId = Mage::app()->getStore()->getId();
$userModel = Mage::getModel('admin/user');
$userModel->setUserId(0);
Mage::getSingleton('admin/session')->setUser($userModel);

// MySQL Connection
$SqlCon = new mysqli();
$SqlCon->Connect('dnliverep1.cp9ve0szhrvj.us-east-2.rds.amazonaws.com', 'magento_admin', '329gvqbu42', 'magento');
// initialize variables
$count_catalogs = 0;
$count_product_catalogs = 0;
$count_engagement_catalog = 0;
$count_custom_catalog = 0;

// Date
$start_date = date('Y-m-d',strtotime('now -1 day'));
$end_date = date('Y-m-d',strtotime('now -1 day'));

if(isset($argv[1])) {
  $start_date = $argv[1];
}
if(isset($argv[2])) {
  $end_date = $argv[2];
}
$filename = $_SERVER['HOME'].'/html/var/report/catalog_labels_' . $start_date . '.csv';

// Report query
$query = "
	SELECT
		first_name,
		last_name,
		address_1 as address1,
		address_2 as address2,
		city,
		region as state,
		postal_code as zip,
		country_id as country,
		send_product_catalog,
		send_engagement_catalog,
		send_custom_catalog
	FROM
		visitor_submissions
	WHERE
		submitted_at between '".$start_date." 00:00:00' AND '".$end_date." 23:59:59'
	AND
	(
		send_product_catalog = '1'
	OR
		send_engagement_catalog = '1'
	OR
		send_custom_catalog = '1'
	);
";
// csv
$csv_data[0] = array(
	'first_name',
	'last_name',
	'address1',
	'address2',
	'city',
	'state',
	'zip',
	'country',
	'cata'
);

$rows = $SqlCon->Query($query);
foreach ($rows as $row) {

	$row['cata'] = null;

	if($row['send_product_catalog']) {
		//$row['cata'] .= 'ca';
		$count_catalogs++;
		$count_product_catalogs++;
	}
	if($row['send_engagement_catalog']) {
		//$row['cata'] .= 'es';
		$count_catalogs++;
		$count_engagement_catalog++;
	}
	if($row['send_custom_catalog']) {
		//$row['cata'] .= 'cu';
		$count_catalogs++;
		$count_custom_catalog++;
	}

	//print $row['last_name']." ".str_replace('&#39;',"\'",$row['last_name'])."\n";	
	$csv_data[] = array(
		str_replace('&#39;',"\'",$row['first_name']),
		str_replace('&#39;',"\'",$row['last_name']),
		$row['address1'],
		$row['address2'],
		$row['city'],
		$row['state'],
		$row['zip'],
		$row['country'],
		$row['cata']
	);

}

$csv_file=new Varien_File_Csv();
$csv_file->saveData($filename,$csv_data);

$email = new Zend_Mail();

$email->addTo('epasek@forevercompanies.com')
	->addTo('labels@forevercompanies.com')
	->addTo('itsupport@diamondnexus.com')
	->setFrom('itsupport@diamondnexus.com')
	->setReplyTo('itsupport@diamondnexus.com')
	->setSubject('Catalog Labels - ' . $start_date);

if( count($rows) > 0 ) {

	$email->setBodyHtml(
		"Catalog Labels Report - " . $start_date . "\n\n" .
		
		"Total Labels: " . count($csv_data) . "\n" .
		"Total Catalogs: " . intval($count_catalogs) . "\n" .

		"Products Catalog: " . intval($count_product_catalogs) . "\n" .
		"Engagement Catalog: " . intval($count_engagement_catalog) . "\n" .
		"Custom Catalog: " . intval($count_custom_catalog) . "\r\n"
	);
	
	$content = file_get_contents($filename);
	$attachment = new Zend_Mime_Part($content);
	$attachment->type = mime_content_type($filename);
	$attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
	$attachment->encoding = Zend_Mime::ENCODING_BASE64;
	$attachment->filename = 'catalog_labels_' . $start_date . '.csv';

	$email->addAttachment($attachment);
		
} else {
	
	$email->setBodyHtml(
		"Catalog Labels Report - " . $start_date . "\n\n" .
		"No labels today"
	);	
}

$email->send();
?>
