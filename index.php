<?php
ini_set('max_execution_time', 0);
if(isset($_COOKIE['token'])) {
    header("Location: importData.php"); 
    exit();
}

require './api/common/header.php';
header("Strict-Transport-Security: max-age=15768000");
header('Access-Control-Allow-Origin', "lookuptools-dev.informamarkets.com, lookuptools.informamarkets.com");
header("X-XSS-Protection: 0");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer");
header("Referrer-Policy: same-origin");
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header("Expires: 0"); 
header("X-Frame-Options: SAMEORIGIN");
header("X-Frame-Options: DENY");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
header('Access-Control-Max-Age: 86400');
header('Access-Control-Request-Headers: X-Custom-Header');
header('Access-Control-Allow-Headers: x-requested-with, Content-Type, origin, authorization, accept, client-security-token');
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


?>
<style>
.container-login100 {
    background: #11a7d9 !important;
}
.wrap-login100 {
    width: 80% !important;
    background: #fff;
    display: flex;
    justify-content: center !important;
    align-items: center;
    padding: 0px  !important;
}
.login100-form {
    width: 290px;
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-flow: column;
    min-height: 75vh;
    padding: 4em 0px;
}
.login100-form-title {
	padding-bottom: 30px !important;
}
</style>
<form class="login100-form validate-form" method="post" id="login_form" autocomplete="off">
<span class="login100-form-title">
Member Login
</span>

<div class="error"></div>
<div class="wrap-input100 validate-input" data-validate="Valid email is required">
<input class="input100" type="text" id="email" name="email" placeholder="Email">
<span class="focus-input100"></span>
<span class="symbol-input100">
<i class="fa fa-envelope"></i>
</span>
</div>
<div class="wrap-input100 validate-input" data-validate="Password is required">
<input class="input100" type="password" id="password" name="password" placeholder="Password">
<span class="focus-input100"></span>
<span class="symbol-input100">
<i class="fa fa-lock" aria-hidden="true"></i>
</span>
</div>
<div class="container-login100-form-btn">
<button type="submit" class="login100-form-btn" name="login_user">
Login
</button>
</div>
<div class="text-center p-t-12">

</div>
<div class="text-center p-t-136">

</div>
</form>
<?php

require './api/common/footer.php';

?>
