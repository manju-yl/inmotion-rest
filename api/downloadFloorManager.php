<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/boothdetails.php';
require "../start.php";

//database connection
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

//get booth object
$boothdetails = new boothdetails($conn); 
//get booths having missed records
$stmt = $boothdetails->getEmptyBoothDetails(); 

$num = $stmt->rowCount();  
//check if records > 0
if ($num > 0) {
    echo "<select id='flooreventselection' name='flooreventselection'>";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row); 
            echo "<option>" . $event_id . "</option>";
        }
    echo "</select>";
}else{
    echo "false"; 
           
}
