<?php
	require_once '/home/admin/html/app/Mage.php';
	Mage::app();

	# set the time zone to CST
	date_default_timezone_set('America/Chicago'); 

	# get the current timestamp
	$now = time();
	
	# day of the week
	$day = date('N',$now);
	
	# hour of the day
	$hour = date('G',$now);
	
	$today = date('m-d-Y', $now);

	$holidays = array(
		'01-01-2015',
		'01-02-2015'
	);
	
	# first we check if today is a holiday
	if( in_array($today, $holidays) == true ) {
		echo "Holiday ignoring text alerts" . "\n";
		exit;
	}
	
	if( $day == 6 ) { // Saturday
		# Saturday we send no text alerts
		echo "Saturday ignoring text alerts" . "\n";
		exit;
	} elseif( $day == 7 ) { // Sunday
		# Sunday we send alerts from 8PM CST until 9PM
		if( $hour != 20 ) {
			echo "Sunday ignoring text alerts" . "\n";
			exit;
		}
	} else {
		# Weekdays we send alerts 6AM - 7PM CST
		if( $hour < 7 || $hour >= 19 ) {
			echo "Weekday ignoring text alerts" . "\n";
			exit;
		}
	}
	
	$w = Mage::getSingleton('core/resource')->getConnection('core_write');

	# get the timestamp minus 0.5 hours
	$time = time() - 1800;

	# create a sql formatted date 2013-11-07 17:10:27
	$stamp = date('Y-m-d H:i:s',$time);

	$sql = "select increment_id,created_at,action	FROM	magento.diamondnexus_webservices_event	WHERE	(imported = 0 OR fetched = 0)	AND	created_at < '".$stamp."'
			ORDER BY
				created_at DESC;";

	$result = $w->query($sql);

        if( $result->rowCount() ) {

                mail(
                        '2624080870@txt.att.net,'. // Paul B
                        '4146280757@vtext.com,'. // Edie P
                        '4143059315@vtext.com', // Charles W
                        'Critical: Slow Order Import Issue',
                        "$row->count  Events not imported for more than 0.5 hours."
                );

			$body = '<table border="1">'."\r\n";
			$body .= '<tr><th>Order ID</th><th>Created At</th></tr>'."\r\n";
			$body .= '<tr><td>'.$row->increment_id.'</td><td>'.$row->created_at.'</td><td>'.$row->action.'</td></tr>'."\r\n";

			while($row = $result->fetch(PDO::FETCH_OBJ))
			{
				$body .= '<tr><td>'.$row->increment_id.'</td><td>'.$row->created_at.'</td><td>'.$row->action.'</td></tr>'."\r\n";
			}

			$body .= '</table>'."\r\n";

			mail(
					'it@forevercompanies.com',
					'Critical: Slow Order Import Issue',
					$body,
					'Content-type:text/html'
			);
        }

	echo("Notification script done..." . "\n");
