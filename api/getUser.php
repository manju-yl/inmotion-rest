<?php

include_once './config/database.php';
include_once './model/user.php';
require "../vendor/autoload.php";
require "./common/headers.php";
require "../start.php";

use \Firebase\JWT\JWT;

$secret_key = $_ENV['JWT_SECRET'];
//get database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
//get filter data from input request
$data = json_decode(file_get_contents("php://input"));
//get authorization header
$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);

$arr = explode(" ", $authHeader);

/* echo json_encode(array(
  "message" => "sd" .$arr[1]
  )); */

$jwt = $arr[1];
//check if jwt token exists
if ($jwt) {

    try {
        //decode jwt token
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

        $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);
        //get user object
        $user = new User($conn);
        //get user details
        $stmt = $user->getUser($email);
        $num = $stmt->rowCount();
        //check if records > 0
        if ($num > 0) {
            $user_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = array(
                    "id" => $id,
                    "firstName" => $first_name,
                    "lastName" => $last_name
                );

                array_push($user_arr, $user_item);
            }

            // set response code - 200 OK
            http_response_code(200);

            // show user data in json format
            echo json_encode($user_arr);
        } else {

            // set response code - 404 Not found
            http_response_code(404);

            // tell the `user no data found
            echo json_encode(
                    array("message" => "No Users found.")
            );
        }
    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}



