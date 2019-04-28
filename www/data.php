<?php

$secret = 'potato';
$token = $_GET['token'];

$data = [ 'Test php', 'status' => 'OK' ];
if( isset($token) && $token === $secret) {
	$data = [];

	// Reading Connection File
	$file1 = '/logs/dhcp.leases';
	$connection = file_get_contents($file1);

	$data['connections'] = [];
	foreach (explode(PHP_EOL, $connection) as $i => $line) {
		$array = explode(' ', $line);
		$data['connections'][$i] = $array;
	}
	
	// Reading Login File
	$file2 = '/logs/logins.txt';
	$logins = file_get_contents($file2);
	
	$data['logins'] = [];
	foreach (explode(PHP_EOL, $logins) as $i => $line) {
		$array = explode(';', $line);
		$data['logins'][$i] = $array;
	}

}

header('Content-Type: application/json');
echo json_encode($data);