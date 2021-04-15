<!DOCTYPE html>
<html lang="en">
<head>
<title>InMotion APP</title>

<link rel="icon" type="image/png" href="public/images/icons/favicon.ico" />

<link rel="stylesheet" type="text/css" href="public/css/main.css">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.0/css/font-awesome.min.css">

<link rel="stylesheet" type="text/css" href="css/animate.css">

<link rel="stylesheet" type="text/css" href="css/hamburgers.min.css">

<link rel="stylesheet" type="text/css" href="css/select2.min.css">

<link rel="stylesheet" type="text/css" href="css/util.css">


</head>
<body>
<div class="limiter">

<div class="container-login100">

<div class="wrap-login100">
<?php
//session_start();
/*if(!isset($_COOKIE['token'])) {
    header("Location: index.php"); 
    exit();
}
*/

if (isset($_GET['logout'])) {
	setcookie('token', '', time() - 3600);
	setcookie('firstname', '', time() - 3600);
	//unset($_COOKIE['firstname']); 
    header("Location: index.php"); 
}

if (isset($_COOKIE['firstname'])){?>
      <p>Welcome <strong><?php echo $_COOKIE['firstname']; ?></strong></p>
      <p> <a href="importData.php?logout='1'" style="color: red;">Logout</a> </p>
<?php } ?>