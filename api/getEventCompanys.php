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
//get filter data from input request
$data = json_decode(file_get_contents("php://input"));
//check if eventId value exists
if ($data->event_id == "" || $data->event_id == null) {
    // set response code
    http_response_code(200);
    // no appointments found
    echo json_encode(
            array("message" => "")
    );
    exit;
}
//get authorization header
$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);

$arr = explode(" ", $authHeader);

//check if event_id iss numeric
if ($data->event_id != "" || $data->event_id != null) {
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
        
        $company_keys = array();
        //get appointment object
        $appointment = new Appointment($conn);
        //get appointment details
        $stmt_appointment_check = $appointment->checkIfEventExists($data);

        //get booth details object
        $boothDetails = new BoothDetails($conn);
        //get appointment details
        $stmt_booth_check = $boothDetails->checkIfEventExists($data);

        //check if records > 0
        if ($stmt_appointment_check->rowCount() > 0 || $stmt_booth_check->rowCount() > 0) {
            //get company details
            $stmt_appointment_company = $appointment->getAllCompanyDetails($data);
            while ($row = $stmt_appointment_company->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

                $appointment_item = array(
                    "option_value" => $co_id
                );
                array_push($company_keys, $appointment_item);
            }
        }

        if (sizeof($company_keys) == 0) {
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
        echo json_encode($company_keys);
    } catch (Exception $e) {

        http_response_code(401);

        echo json_encode(array(
            "message" => "Access denied.",
            "error" => $e->getMessage()
        ));
    }
}
