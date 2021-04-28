<?php

include_once './config/database.php';
include_once './model/user.php';
require "../vendor/autoload.php";
require "./common/headers.php";
require "../start.php";

use \Firebase\JWT\JWT;

header('Content-type: application/json');
//database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
//get filter data from input request
$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$password = $data['password'];
//get user object
$user = new User($conn);
//get user details by emailId
$stmt = $user->getUser($email);
$num = $stmt->rowCount();
//check if email and password is null
if ($email != "" && $password != "") {
    //check if records > 0
    if ($num > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $row['id'];
        $firstname = $row['first_name'];
        $lastname = $row['last_name'];
        $password2 = $row['password'];
        //check if password matches
        if (password_verify($password, $password2)) {
            $secret_key = $_ENV['JWT_SECRET'];
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
            //encode the jwt token
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
            echo json_encode(array('status' => 401, 'error' => 'Incorrect Email Address or Password.'));
            exit;
        }
    } else {
        echo json_encode(array('status' => 401, 'error' => 'Incorrect Email Address or Password.'));
        exit;
    }
}
