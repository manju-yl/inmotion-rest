<?php

include_once './config/database.php';
require "../vendor/autoload.php";
require "./common/headers.php";
use \Firebase\JWT\JWT;

$secret_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9";
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);

$arr = explode(" ", $authHeader);

/* echo json_encode(array(
  "message" => "sd" .$arr[1]
  )); */

$jwt = $arr[1];

if ($jwt) {

    try {

        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

        // Access is granted. Add code of the operation here 

        echo json_encode(array(
            "message" => "Access granted: Token is valid"
                //"error" => $e->getMessage()
        ));
    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}
