<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
require "../start.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

if(isset($_POST['export'])){
$event_id = filter_var($_POST['eventselection'], FILTER_SANITIZE_NUMBER_INT); 

//get appointment object
$appointment = new appointment($conn);  
//get appointments having missed records by event_id
$stmt = $appointment->downloadAppointmentDetails($event_id); 

$fileName = $event_id .' - appointment-'.time();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();


$styleArray = array(
    'font' => array(
        'bold' => true
    )
);
$sheet->setCellValue('A1', 'COID')->getStyle('A1')->applyFromArray($styleArray);
$sheet->setCellValue('B1', 'EventId')->getStyle('B1')->applyFromArray($styleArray);
$sheet->setCellValue('C1', 'Re-Sign Appt Date Text')->getStyle('C1')->applyFromArray($styleArray);
$sheet->setCellValue('D1', 'Re-sign Appt Time Text')->getStyle('D1')->applyFromArray($styleArray);
$sheet->setCellValue('E1', 'Company Name')->getStyle('E1')->applyFromArray($styleArray);

$rowCount = 2;
$num = $stmt->rowCount();
//check if records > 0
if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row); 
            $sheet->setCellValue('A' . $rowCount, $co_id);
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

if(isset($_POST['exportEmptyFloorDetails'])){
$event_id = filter_var($_POST['flooreventselection'], FILTER_SANITIZE_NUMBER_INT); 

//get booth details object
$boothDetails = new BoothDetails($conn); 
//get booths having missed records by event_id
$stmt = $boothDetails->downloadBoothDetails($event_id);
$fileName = $event_id .' - floormanager-'.time();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$styleArray = array(
    'font' => array(
        'bold' => true
    )
);

$sheet->setCellValue('A1', 'CoID')->getStyle('A1')->applyFromArray($styleArray);
$sheet->setCellValue('B1', 'EventId')->getStyle('B1')->applyFromArray($styleArray);
$sheet->setCellValue('C1', 'Exhibiting As')->getStyle('C1')->applyFromArray($styleArray);
$sheet->setCellValue('D1', 'Booth Number')->getStyle('D1')->applyFromArray($styleArray);
$sheet->setCellValue('E1', 'Company Contact First Name')->getStyle('E1')->applyFromArray($styleArray);
$sheet->setCellValue('F1', 'Company Contact Last Name')->getStyle('F1')->applyFromArray($styleArray);
$sheet->setCellValue('G1', 'Company Email')->getStyle('G1')->applyFromArray($styleArray);
$sheet->setCellValue('H1', 'Hall')->getStyle('H1')->applyFromArray($styleArray);
$sheet->setCellValue('I1', 'Floor Manager')->getStyle('I1')->applyFromArray($styleArray);
$sheet->setCellValue('J1', 'Phone')->getStyle('J1')->applyFromArray($styleArray);
$sheet->setCellValue('K1', 'GES ESE')->getStyle('K1')->applyFromArray($styleArray);
$sheet->setCellValue('L1', 'Text Number')->getStyle('L1')->applyFromArray($styleArray);


$rowCount = 2;
$num = $stmt->rowCount();
//check if records > 0
if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row); 
            $sheet->setCellValue('A' . $rowCount, $co_id);
            $sheet->setCellValue('B' . $rowCount, $event_id);
            $sheet->setCellValue('C' . $rowCount, $company_name);
            $sheet->setCellValue('D' . $rowCount, $booth);
            $sheet->setCellValue('E' . $rowCount, $company_contact_first_name);
            $sheet->setCellValue('F' . $rowCount, $company_contact_last_name);
            $sheet->setCellValue('G' . $rowCount, $company_email);
            $sheet->setCellValue('H' . $rowCount, $hall);
            $sheet->setCellValue('I' . $rowCount, $fm_name);
            $sheet->setCellValue('J' . $rowCount, $fm_phone);
            $sheet->setCellValue('K' . $rowCount, $ges_ese);
            $sheet->setCellValue('L' . $rowCount, $fm_text_number);

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
