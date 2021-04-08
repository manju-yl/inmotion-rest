<?php

include_once './config/database.php';
include_once './model/user.php';
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

        $email = filter_var($data->email, FILTER_SANITIZE_EMAIL);

        $user = new User($conn);
        $stmt = $user->getUser($email);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $user_arr = array();

            // retrieve our table contents
            // fetch() is faster than fetchAll()
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // extract row
                // this will make $row['name'] to
                // just $name only
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

            // show products data in json format
            echo json_encode($user_arr);
        } else {

            // set response code - 404 Not found
            http_response_code(404);

            // tell the `user no products found
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



