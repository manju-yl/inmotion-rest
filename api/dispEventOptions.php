<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
require "./common/headers.php";
require "../start.php";

use \Firebase\JWT\JWT;

$secret_key = $_ENV['JWT_SECRET'];
//database connection
$databaseService = new DatabaseService();
$conn = $databaseService->getConnection();
//get filter data from input request
$data = json_decode(file_get_contents("php://input"));
//get authorization header
$authHeader = filter_input(INPUT_SERVER, 'HTTP_AUTHORIZATION', FILTER_SANITIZE_STRING);

$arr = explode(" ", $authHeader);
//check if jwt token exists
$jwt = $arr[1]; 

if ($jwt) {
    try {
        //decode the jwt token
        $decoded = JWT::decode($jwt, $secret_key, array('HS256'));
        
        $event_keys = array();

        //get appointment object
		$appointment = new appointment($conn);  
		//get all appointment and floor manager events
		$stmt = $appointment->getAllEventDetails(); 

        $num = $stmt->rowCount(); 
		//check if records > 0
		if ($num > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);

                $event_items = array(
                    "option_value" => $event_id
                );
                array_push($event_keys, $event_items);
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
}