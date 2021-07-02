<?php

include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
require "../vendor/autoload.php";
require "./common/headers.php";
require "../start.php";

use \Firebase\JWT\JWT;

$secret_key = $_ENV['JWT_SECRET'];
//database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

//validate url
if (filter_var($_ENV['SERVER_URL'].$_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
    //set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(
            array("message" => "")
    );
    exit;
}

//get filter data from input request
$data = json_decode(file_get_contents("php://input"));
//get authorization header
$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);
$arr = explode(" ", $authHeader);
//check if eventId value exists
if ($data->event_id == "" || $data->event_id == null ) {
    // set response code - 404 Not found
    http_response_code(404);
    // no appointments found
    echo json_encode(
            array("message" => "")
    );
    exit;
}
//check if event_id iss numeric
if ($data->event_id != "" || $data->event_id != null ) {
    if (!is_numeric($data->event_id) || !is_numeric($data->event_id)) {
     // set response code - 404 Not found
    http_response_code(404);
    // no appointments found
    echo json_encode(
            array("message" => "")
    );
    exit;
    }
}

//check if jwt token exists
$jwt = $arr[1];

if ($jwt) {

    try {
      
        //decode the jwt token
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        
        if ($data->deleteFlag == "appointment") {
            //get appointment object
            $appointment = new Appointment($conn);
            //get appointment details
            $stmt = $appointment->checkIfEventExists($data);
        }
        elseif ($data->deleteFlag == "floorManager") { 
            //get booth details object
            $boothDetails = new BoothDetails($conn);
            //get appointment details
            $stmt = $boothDetails->checkIfEventExists($data);
        } else {
            // set response code - 404 Not found
            http_response_code(404);

            // no appointments found
            echo json_encode(
                    array("message" => "Invalid selection.")
            );
        }
        
        
        $num = $stmt->rowCount();
        //check if records > 0
        if ($num > 0) {
            if ($data->deleteFlag == "appointment") {
                $appointment->deleteEventData($data);
            }
            if ($data->deleteFlag == "floorManager") {
                $boothDetails->deleteEventData($data);
            }
            // set response code - 200 OK
            http_response_code(200);

            // no appointments found
            echo json_encode(
                    array("message" => "Data is deleted successfuly.")
            );
        } else {

            // set response code - 404 Not found
            http_response_code(404);

            // no appointments found
            echo json_encode(
                    array("message" => "No Data found.")
            );
        }
    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}else{
    // set response code - 400 Bad Request
    http_response_code(400);
    echo json_encode(
            array("message" => "")
    );
    exit;
}



