<?php

include_once './config/database.php';
include_once './model/boothdetails.php';
require "../vendor/autoload.php";
require "./common/headers.php";

use \Firebase\JWT\JWT;

$secret_key = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9";
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

$data = json_decode(file_get_contents("php://input"));

if ($data->event_id == "" || $data->event_id == null ) {
    // set response code - 404 Not found
    http_response_code(404);
    // no appointments found
    echo json_encode(
            array("message" => "EventId is required.")
    );
    exit;
}
if ($data->event_id != "" || $data->event_id != null ) {
    if (!is_numeric($data->event_id) || !is_numeric($data->event_id)) {
     // set response code - 404 Not found
    http_response_code(404);
    // no appointments found
    echo json_encode(
            array("message" => "EventId should be numberic.")
    );
    exit;
    }
}
if ($data->company_id != "" || $data->company_id != null ) {
    if (!is_numeric($data->company_id) || !is_numeric($data->company_id)) {
     // set response code - 404 Not found
    http_response_code(404);
    // no appointments found
    echo json_encode(
            array("message" => "CompanyId should be numberic.")
    );
    exit;
    }
}

$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);

$arr = explode(" ", $authHeader);

$jwt = $arr[1];

if ($jwt) {

    try {

        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));

        $boothDetails = new BoothDetails($conn);
        $stmt = $boothDetails->getBoothDetails($data);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $user_arr = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = array(
                    "eventId" => $event_id,
                    "eventName" => $event_name,
                    "companyId" => $co_id,
                    "companyName" => $company_name,
                    "booth" => $booth,
                    "hall" => $hall,
                    "fm_name" => $fm_name,
                    "fm_text_number" => $fm_text_number
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

            // no appointments found
            echo json_encode(
                    array("message" => "No Booth details and Floor Manager details found.")
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



