<?php

$timeout = $argv[1];
$url = $argv[2];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

$data = curl_exec($ch);

# checking for redirect
if( strlen($data) == 0 && $data === true ) {

	$curlinfo = curl_getinfo($ch);

	if( isset($curlinfo['redirect_url']) ) {

		curl_close($ch);

		print "redirect";
		exit;

	}
}

curl_close($ch);

print $data;
