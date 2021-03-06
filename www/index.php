<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-type" content="text/html; charset=utf-8"/>

	<title>JASYP FR33 WIFI - Login</title>

	<link rel="stylesheet" type="text/css" href="styles.css">

	<!--<script type="text/javascript" src="main.js" defer></script>-->
	
</head>
<body>
	
	<div class="wrapper">
		<div  class="title">
			<h2> JASYP FR33 WIFI </h2>
		</div>

		<?php if(isset($_GET['error']) ): ?>
			<div class="error">
				<h3> Internal server error :( !!! <br> Service temporarily unavailable </h3>
			</div>
		<?php endif; ?>  
		
		<form id="loginForm" method="POST" action="http://172.16.0.1:8080/dologin.php">
			<div class="form-group">
				<label>User: </label>
				<input type="email" name="user" placeholder="Email" maxlength="50" required>
			</div>
			<div class="form-group">
				<label>Password: </label>
				<input type="password" name="password" placeholder="Password" maxlength="30" required>
			</div>
			
			<input type="submit" value="Login">
		</form>
	</div>
	
</body>
</html>
