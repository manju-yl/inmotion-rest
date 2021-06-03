<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
require "../start.php";

//database connection
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

//get appointment object
$appointment = new appointment($conn);  
//get all appointment and floor manager events
$stmt = $appointment->getAllCompanyDetails(); 

$num = $stmt->rowCount(); 
//check if records > 0
if ($num > 0) {
    echo "<select id='companyselection' name='companyselection'>";
    echo "<option>Select Company</option>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            echo "<option>" . $co_id . "</option>";
        }
    echo "</select>";
}else{
    echo "false"; 
           
}
