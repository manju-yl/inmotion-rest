<?php

include_once './config/database.php';
include_once './model/user.php';
require "./common/headers.php";
require "../start.php";
//get database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
//get filter data from input request
$data = json_decode(file_get_contents("php://input"));

//request parameters
$firstName = filter_var($data->first_name, FILTER_SANITIZE_STRING);
$lastName = filter_var($data->last_name, FILTER_SANITIZE_STRING);
$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
$password = $data->password;
//check if email empty
if ($email == "" || $email == null ) {
    // set response code - 200 Not found
    http_response_code(200);
    // password cannot be empty
    echo json_encode(
            array("message" => "Email id cannot be empty")
    );
    exit;
}
//check if password empty
if ($password == "" || $password == null ) {
    // set response code - 200 Not found
    http_response_code(200);
    // email id cannot be empty
    echo json_encode(
            array("message" => "Password cannot be empty")
    );
    exit;
}

//get user object
$user = new User($conn);
//add user data
$stmt = $user->addUser($firstName, $lastName, $email, $password);

if ($stmt->execute()) {

    http_response_code(200);
    echo json_encode(array("message" => "User was successfully registered."));
} else {
    http_response_code(400);

    echo json_encode(array("message" => "Unable to register the user."));
}
