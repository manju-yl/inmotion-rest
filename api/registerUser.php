<?php

include_once './config/database.php';
include_once './model/user.php';
require "./common/headers.php";

$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

$firstName = filter_var($data->first_name, FILTER_SANITIZE_STRING);
$lastName = filter_var($data->last_name, FILTER_SANITIZE_STRING);
$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;

$user = new User($conn);
$stmt = $user->addUser($firstName, $lastName, $email, $password);

if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(array("message" => "User was successfully registered."));
} else {
    http_response_code(400);

    echo json_encode(array("message" => "Unable to register the user."));
}
