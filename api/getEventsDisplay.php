<?php

include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
require "../vendor/autoload.php";
require "./common/headers.php";
require "../start.php";

use \Firebase\JWT\JWT;

$secret_key = $_SERVER['JWT_SECRET'];
//database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();

//validate url
if (filter_var($_SERVER['SERVER_URL'].$_SERVER['REQUEST_URI'], FILTER_VALIDATE_URL, FILTER_FLAG_QUERY_REQUIRED)) {
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
if ($data->options == "" || $data->options == null) {
    // set response code - 404 Not found
    http_response_code(200);
    // no appointments found
    echo json_encode(
            array("message" => "")
    );
    exit;
}

//check if jwt token exists
$jwt = $arr[1];
if ($jwt) {
    try {
        //decode the jwt token
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        
        $event_keys = array();

        if($data->options == "appointment"){
        //get appointment object
        $appointment = new Appointment($conn);
        //get appointment details
        $stmt_appointment_check = $appointment->getAllAppointmentEventDetails();
        //check if records > 0
        if ($stmt_appointment_check->rowCount() > 0) {
            while ($row = $stmt_appointment_check->fetch(PDO::FETCH_ASSOC)) {
                extract($row); 

                $appointment_item = array(
                    "option_value" => $event_id
                );
                array_push($event_keys, $appointment_item);
            }
        }
        }

        if($data->options == "floorManager"){
        //get booth details object
        $boothDetails = new BoothDetails($conn);
        //get appointment details
        $stmt_booth_check = $boothDetails->getAllBoothEventDetails();
        if ($stmt_booth_check->rowCount() > 0) {
            while ($row = $stmt_booth_check->fetch(PDO::FETCH_ASSOC)) {
                extract($row);

                $floor_item = array(
                    "option_value" => $event_id
                );
                array_push($event_keys, $floor_item);
            }
            
        }
        }
        
        if (sizeof($event_keys) == 0) {
            // set response code - 404 Not found
            http_response_code(404);

            // no appointments found
            echo json_encode(
                    array("message" => "No Data found.")
            );
            exit;
        }

        // set response code - 200 OK
        http_response_code(200);

        // show user data in json format
        echo json_encode($event_keys);
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



