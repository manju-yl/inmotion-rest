<?php

require "../vendor/autoload.php";
include_once './config/database.php';
require "../start.php";

//database connection
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();
//uploaded file expiry days
$day = $_ENV['UPLOAD_FILE_EXPIRY_DAYS'];
if ($day != "") {
    $days = $day;
} else {
    $days = 0;
}
$days  =   ($days == 0) ? 60 : $days; 
$query = "SELECT filename FROM `uploads` WHERE datediff(now(), created_date) >= $days";
// prepare query statement
$stmt = $conn->prepare($query); 
// execute query
$stmt->execute(); $stmt->debugDumpParams(); 
$num = $stmt->rowCount();
if($num>0){
    $delete_arr = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $delete_arr_item = array(
            "filename" => $filename
        );
        
        array_push($delete_arr, $delete_arr_item);
    }
    foreach($delete_arr as $key => $delete_file){
        unlink($delete_file['filename']);
    }
    $deleteQuery = "DELETE FROM uploads 
                   WHERE datediff(now(), created_date) >= $days";
    // prepare query statement
    $stmt = $conn->prepare($deleteQuery); 
    // execute query
    $stmt->execute();  $stmt->debugDumpParams();
}
 
?>
