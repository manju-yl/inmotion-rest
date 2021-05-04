<!DOCTYPE html>
<html lang="en">
<head>
<title>InMotion APP</title>

<link rel="icon" type="image/png" href="public/images/icons/favicon.ico" />

<link rel="stylesheet" type="text/css" href="public/css/main.css">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">

<script src="public/scripts/jquery-3.2.1.min.js"></script>

</head>
<body>
<div class="limiter">

	<div class="container-login100">

		<div class="wrap-login100">
			<?php

			if (isset($_GET['logout'])) {
				setcookie('token', '', time() - 3600);
				setcookie('firstname', '', time() - 3600);
				setcookie('userId', '', time() - 3600);
				header("Location: index.php"); 
			}

			if (isset($_COOKIE['firstname'])){?>
				<p>Welcome <strong><?php echo $_COOKIE['firstname']; ?></strong></p>
				<p> <a href="importData.php?logout='1'" style="color: red;">Logout</a> </p>
			<?php  }  ?>
