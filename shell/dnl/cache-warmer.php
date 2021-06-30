<?php

function addProcess($index = 0, $process = 0, $timeout = 10) {

	global $i;
	global $count;
	global $aHandle;
	global $aUrl;
	global $aStatus;
	global $aTimeout;

	# add a new process
	$aHandle[$process] = popen("/usr/bin/php " . getcwd() . "/cache-build.php " . $timeout . " " . $aUrl[$index], "r");

	$aStatus[$process] = $aUrl[$index];

	echo "$i of $count: reading " . $aStatus[$process] . "\n";

	# reset the timeout
	$aTimeout[$process] = $timeout;

}

function sitemapToArray($sitemapUrl = null){

	$aTemp = array();

	# get the sitemap XML
	$xmlString = file_get_contents($sitemapUrl);

	$xml = simplexml_load_string($xmlString);

	# convert to JSON object
	$json = json_encode($xml);

	# convert to array
	$array = json_decode($json,TRUE);

	foreach( $array['url'] as $url ) {
	        $aTemp[] = $url['loc'];
	}

	return $aTemp;
}

$aUrl = array();
$aHandle = array();
$aTimeout = array();
$aStatus = array();

$aSuccess = array();
$aRedirect = array();
$aFailure = array();
$aLog = array();

# set the number of threads the script will use
$threads = 20;

# set the number of seconds before a timeout
$timeout = 10;

# get the sitemap parameter
$sitemapUrl = $argv[1];

if( strlen($sitemapUrl) > 6 ) {
	# get the sitemap XML and convert to array
	$aUrl = sitemapToArray($sitemapUrl);
} else {
	echo "Please specify a sitemap url parameter\n";
	exit;
}

$count = count($aUrl);

echo $count . " found\n";

$i = 0;

# loop through all urls
while( $i < $count ) {

	for( $j=0; $j<=$threads; $j++ ) {

		# make sure the processes don't get created if they are not needed
		if ( $i > $count ) {
			break 2;
		}

		if( isset($aHandle[$j]) == true ) {

			$read = fread($aHandle[$j], 192);

			$aTimeout[$j] -= 1;

			# check for a redirect
			if( $read == "redirect" ) {
				$aRedirect[] = $aStatus[$j];
			} elseif ( strlen($read) > 0 ) {
				$aSuccess[] = $aStatus[$j];
			}

			if( $aTimeout[$j] <= 0 ) {
				$aFailure[] = $aStatus[$j];
			}

			# of the thread is complete
			if( strlen($read) > 0 || $aTimeout[$j] <= 0 ) {

				$aLog[] = $timeout - $aTimeout[$j];

				# move to the next row first here
				$i++;

				# close process
				pclose($aHandle[$j]);

				addProcess( $i, $j, $timeout );

			}

		} else {

			addProcess( $i, $j, $timeout );

			# move to next row
			$i++;

		}
	}

	# sleep for one second
	sleep(1);
}

mail(
	"cachewarmer@diamondnexus.com",
	"Cache Warmer Report - " . $sitemapUrl,

	"Sitemap URL " . $sitemapUrl . "\n" .
	"Crawled " . (count($aLog) + $threads) . " urls\n" .
	"Average load speed " . array_sum($aLog) / count($aLog) . "\n\n" .
	"Redirects: " . count($aRedirect) . "\n   " . implode( "\n        ", $aRedirect  ) . "\n\n" .
	"Failures: " . count($aFailure) . "\n	" . implode( "\n	", $aFailure  ) . "\n\n".
	"From: " . gethostname() . ":" . getcwd() . "/cache-warmer.php",
	"From: customerservice@diamondnexus.com;"
);

