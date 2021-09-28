<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
require "./common/headers.php";
require "../start.php";

//database connection
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

//get appointment object
$appointment = new appointment($conn);  
//get all appointment and floor manager events
$stmt = $appointment->getAllAppointmentEventDetails(); 

$num = $stmt->rowCount(); 
//check if records > 0
if ($num > 0) {
    echo "<select id='eventdeletion' name='eventdeletion'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            echo "<option>" . $event_id . "</option>";
        }
    echo "</select>";
}else{
    echo "false"; 
           
}
