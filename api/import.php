<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
require "./common/headers.php";

use Phppot\DataSource;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

header('Content-type: application/json');
$databaseService = new DatabaseService(); 
$conn = $databaseService->getConnection();

$user_id = "";
if(isset($_COOKIE['userId'])){
    $user_id = $_COOKIE['userId'];
}

//If resign appointment radio button is clicked
if (isset($_POST["resignappintments"])) { 
    //check if file is selected
    if($_FILES['file']['name'] !=""){
    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]; 
    //check if selected file type is proper
    if (in_array($_FILES["file"]["type"], $allowedFileType)) { 
        if (!file_exists('uploads')) {
            mkdir('./uploads', 0777, true);
        }
        $targetPath = 'uploads/' . $_FILES['file']['name'];
        $ret = move_uploaded_file($_FILES['file']['tmp_name'], $targetPath);
	
	//check if selected files are moved to temporary folder        
	if (!$ret) {
            $message = '<div class="errorMessage errormsgWrapperDi">There is an error in uploading the file.</div>';
            echo json_encode(array('status' => 200, 'error' => $message)); exit;
        }
        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); 
        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

	    //loop through the excel data
        for ($i = 1; $i <= $sheetCount-1; $i ++) {
            $company_id = $spreadSheetAry[$i][0];
            $event_id = $spreadSheetAry[$i][1];
            $day = $spreadSheetAry[$i][2];
            $time = $spreadSheetAry[$i][3];
            $company_name = $spreadSheetAry[$i][4];

            $appointment = new appointment($conn);  
            $stmt = $appointment->addOrUpdateAppointment($event_id, $company_id, $day, $time, $company_name, $user_id);
            $stmt->execute();
        }

            $message = '<div class="alert alert-success">Appointment was successfully registered.</div>';
            echo json_encode(array('status' => 200, 'error' => $message)); exit;
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid File Type. Upload Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please Select Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
}

//If floor manager lookup radio button is clicked
if (isset($_POST["floormanager"])) { 
    //check if file is selected
    if($_FILES['myfile']['name'] !=""){
    $allowedFileType = [
        'application/vnd.ms-excel',
        'text/xls',
        'text/xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]; 
    //check if selected file type is proper
    if (in_array($_FILES["myfile"]["type"], $allowedFileType)) { 
        if (!file_exists('uploads')) {
            mkdir('./uploads', 0777, true);
        }
        $targetPath = 'uploads/' . $_FILES['myfile']['name'];
        $ret = move_uploaded_file($_FILES['myfile']['tmp_name'], $targetPath);
	
	//check if selected files are moved to temporary folder         
	if (!$ret) {
            $message = '<div class="errorMessage errormsgWrapperDi">There is an error in uploading the file.</div>';
            echo json_encode(array('status' => 200, 'error' => $message)); exit;
        }
        $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); 

        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();
        $sheetCount = count($spreadSheetAry);

	//loop through the excel data
        for ($i = 1; $i <= $sheetCount; $i ++) {
            $company_id = $spreadSheetAry[$i][0];
            $event_id = $spreadSheetAry[$i][1];
            $company_name = $spreadSheetAry[$i][2];
            $booth = $spreadSheetAry[$i][3];
            $company_contact_firstname = $spreadSheetAry[$i][4];
            $company_contact_lastname = $spreadSheetAry[$i][5];
            $company_email = $spreadSheetAry[$i][6];
            $hall = $spreadSheetAry[$i][7];
            $fm_name = $spreadSheetAry[$i][8];
            $fm_phone = $spreadSheetAry[$i][9];
            $ges_ese = $spreadSheetAry[$i][10];
            $fm_text_number = $spreadSheetAry[$i][11];

            $boothdetails = new boothdetails($conn);  
            $stmt = $boothdetails->addOrUpdateBoothDetails($event_id, $company_id, $company_name, $booth, $company_contact_firstname, $company_contact_lastname, $company_email, $hall, $fm_name, $fm_phone, $ges_ese, $fm_text_number, $user_id);
            $stmt->execute();
        }

            $message = '<div class="alert alert-success">Booth details were successfully registered.</div>';
            echo json_encode(array('status' => 200, 'error' => $message)); exit;
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid File Type. Upload Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please Select Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
}
