<?php
	if (isset($_COOKIE['expireAt'])) { 
		if(time() > base64_decode($_COOKIE['expireAt'])){
			setcookie('token', '', time() - 3600);
			setcookie('expireAt', '', time() - 3600);
			setcookie('firstname', '', time() - 3600);
			setcookie('userId', '', time() - 3600);
			header("Location: index.php"); 
		}
	}
	if (isset($_GET['logout']) || !empty($_GET['logout'])) {
		setcookie('token', '', time() - 3600);
		setcookie('firstname', '', time() - 3600);
		setcookie('userId', '', time() - 3600);
		setcookie('expireAt', '', time() - 3600);
		header("Location: index.php"); 
	} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>InMotion APP</title>

<link rel="icon" type="image/png" href="public/images/icons/favicon.ico" />

<link rel="stylesheet" type="text/css" href="public/css/main.css">

<link rel="stylesheet" type="text/css" href="public/css/font-awesome.min.css">

<script src="public/scripts/jquery-3.5.0.min.js"></script>

</head>
<body>
<div class="limiter">

	<div class="container-login100">

		<div class="wrap-login100">
			<?php

			if (isset($_COOKIE['firstname'])){?>
				<p>Welcome: <strong><?php echo base64_decode($_COOKIE['firstname']); ?></strong></p>
				<p> <a href="importData.php?logout='1'">Logout</a> </p>
			<?php  }  ?>
