<?php

include_once './config/database.php';
include_once './model/user.php';
require "../vendor/autoload.php";
require "./common/headers.php";
use \Firebase\JWT\JWT;
header('Content-type: application/json');
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();
//$data = json_decode(file_get_contents("php://input"),true);
//$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
//$password = $data->password;

$email           = htmlentities($_POST['email']);
$password        = htmlentities($_POST['password']);
$user = new User($conn); 
$stmt = $user->getUser($email);
$num = $stmt->rowCount(); 
if($email != "" && $password != ""){
if ($num > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $id = $row['id'];
    $firstname = $row['first_name'];
    $lastname = $row['last_name'];
    $password2 = $row['password'];

    if (password_verify($password, $password2)) {
        $secret_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9";
        $issuedat_claim = time(); // issued at
        $expire_claim = $issuedat_claim + 86400; // expire time in seconds
        $token = array(
            "iat" => $issuedat_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $id,
                "firstname" => $firstname,
                "lastname" => $lastname,
                "email" => $email
        ));
        //http_response_code(200);
        
        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
                array(
                    "status" => 200,
                    "message" => "Successful generated token.",
                    "jwt" => $jwt,
                    "expireAt" => $expire_claim,
                    "userId" => $id,
		    "firstname" => $firstname
        ));
       exit;
    } else {
        //http_response_code(401);
        //echo json_encode(array("message" => "Login failed.", "password" => $password));
        echo json_encode(array('status' => 401, 'error' => 'Incorrect Email Address or Password.')); exit; 
    }
} else {
       echo json_encode(array('status' => 401, 'error' => 'Incorrect Email Address or Password.')); exit; 
    }
}
