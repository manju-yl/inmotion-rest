
<?php
if(isset($_COOKIE['token'])) {
    header("Location: importData.php"); 
    exit();
}

require './api/common/header.php';


?>
<form class="login100-form validate-form" method="post" id="login_form">
<span class="login100-form-title">
Member Login
</span>

<div class="error"></div>
<div class="wrap-input100 validate-input" data-validate="Valid email is required: ex@abc.xyz">
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