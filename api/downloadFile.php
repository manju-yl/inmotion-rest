<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

if(isset($_POST['export'])){
$event_id = filter_var($_POST['eventselection'], FILTER_SANITIZE_NUMBER_INT); 

//get appointment object
$appointment = new appointment($conn);  
//get appointment details by event_id
$stmt = $appointment->downloadAppointmentDetails($event_id); //$stmt->debugDumpParams(); 

$fileName = 'appointment-'.time();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'COID');
$sheet->setCellValue('B1', 'EVENTID');
$sheet->setCellValue('C1', 'Re-Sign_Appt_Date_Text');
$sheet->setCellValue('D1', 'Re-sign_Appt_Time_Text');
$sheet->setCellValue('E1', 'Company_Name');

$rowCount = 2;
$num = $stmt->rowCount();
//check if records > 0
if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $sheet->setCellValue('A' . $rowCount, $company_id);
            $sheet->setCellValue('B' . $rowCount, $event_id);
            $sheet->setCellValue('C' . $rowCount, $day);
            $sheet->setCellValue('D' . $rowCount, $time);
            $sheet->setCellValue('E' . $rowCount, $company_name);
            $rowCount++; 
        }
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$fileName = $fileName.'.xlsx';
 
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=".$fileName);
header("Cache-Control: max-age=0");
$writer->save('php://output'); exit;
}
