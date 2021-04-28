<?php 
require "../vendor/autoload.php";
include_once './config/database.php';
include_once './model/appointment.php';
include_once './model/boothdetails.php';
require "./common/headers.php";
require "../start.php";

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

        // file path
        $spreadSheet = $Reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();


        // array Count
        $sheetCount = count($spreadSheetAry);
        $flag = 0;
        //$missedRowCount = 0;


        $createArray = array('COID', 'EVENTID', 'Re-Sign_Appt_Date_Text', 'Re-sign_Appt_Time_Text', 'Company_Name');
        $makeArray = array( 'COID' => 'COID', 'EVENTID' => 'EVENTID', 'Re-Sign_Appt_Date_Text' => 'Re-Sign_Appt_Date_Text', 'Re-sign_Appt_Time_Text' => 'Re-sign_Appt_Time_Text', 'Company_Name' => 'Company_Name');
        $SheetDataKey = array(); 
        foreach ($spreadSheetAry as $dataInSheet) {
            foreach ($dataInSheet as $key => $value) {//echo "<pre>"; print_r(trim($value)); print_r($createArray);
                if (in_array(trim($value), $createArray)) {//echo trim($value);
                    $value = preg_replace('/\s*/', '', $value);
                    $SheetDataKey[trim($value)] = $key;
                } 
            }
        } 
        $dataDiff = array_diff_key($makeArray, $SheetDataKey); 
        if (empty($dataDiff)) {
            $flag = 1;
        }

        // match excel sheet column
        if ($flag == 1) {
        
        //loop through the excel data
        for ($i = 1; $i <= $sheetCount-1; $i ++) {
            $CoID = $SheetDataKey['COID'];
            $EVENTID = $SheetDataKey['EVENTID'];
            $date = $SheetDataKey['Re-Sign_Appt_Date_Text'];
            $time = $SheetDataKey['Re-sign_Appt_Time_Text'];
            $Company_Name = $SheetDataKey['Company_Name'];

            $company_id = filter_var(trim($spreadSheetAry[$i][$CoID]), FILTER_SANITIZE_NUMBER_INT);
            $event_id = filter_var(trim($spreadSheetAry[$i][$EVENTID]), FILTER_SANITIZE_NUMBER_INT);
            $day = filter_var(trim($spreadSheetAry[$i][$date]), FILTER_SANITIZE_STRING);
            $time = filter_var(trim($spreadSheetAry[$i][$time]), FILTER_SANITIZE_STRING);
            $company_name = filter_var(trim($spreadSheetAry[$i][$Company_Name]), FILTER_SANITIZE_STRING);
       
            
            $appointment = new appointment($conn);  
            $stmt = $appointment->addOrUpdateAppointment($event_id, $company_id,  $day, $time, $company_name, $user_id);

            
            if ($stmt->execute()) {
                $message = 'Appointment was successfully registered. ';
            } else {
                //$missedRowCount++;
                $message = 'Appointment was successfully registered. '; 
            }
            //$stmt->debugDumpParams();
            }
            
           /* if($missedRowCount > 0){
            $message .= $missedRowCount . " Appointments are not registered due to missed Company Id and EventId in the Excel Sheet. Please insert Company Id and Event Id and upload the Excel File Again.";
        }    */
        $successmessage = '<div class="alert alert-success">'.$message.'</div>';
        echo json_encode(array('status' => 200, 'error' => $successmessage)); exit;
        
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Please import correct file, did not match excel sheet column.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit; 
        }
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
