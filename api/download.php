<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

//get appointment object
$appointment = new appointment($conn);  
//get appointment details
$stmt = $appointment->getEmptyAppointmentDetails(); 

$num = $stmt->rowCount(); 
//check if records > 0
if ($num > 0) {
    echo "<select id='eventselection' name='eventselection'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            echo "<option>" . $event_id . "</option>";
        }
    echo "</select>";
}else{
    echo "false"; 
           
}