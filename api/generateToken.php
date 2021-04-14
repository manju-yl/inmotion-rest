<?php

include_once './config/database.php';
include_once './model/user.php';
require "../vendor/autoload.php";
require "./common/headers.php";
use \Firebase\JWT\JWT;

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;

$user = new User($conn);
$stmt = $user->getUser($email);
$num = $stmt->rowCount();

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

        http_response_code(200);

        $jwt = JWT::encode($token, $secret_key);
        echo json_encode(
                array(
                    "message" => "Successful generated token.",
                    "jwt" => $jwt,
                    "expireAt" => $expire_claim,
                    "userId" => $id
        ));
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed.", "password" => $password));
    }
}
