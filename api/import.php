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
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); 

        // file path
        $spreadSheet = $reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();


        // array Count
        $sheetCount = count($spreadSheetAry);
        $flag = 0;
        $emptyRecordCount = 0;
        $missedRowCount = 0;
        $retArr = array();
        $selectBoxDisplay = "";
        $missedRecordCount = 0;


        $createArray = array('COID', 'EventId', 'Re-Sign Appt Date Text', 'Re-sign Appt Time Text', 'Company Name');
        $makeArray = array( 'COID' => 'COID', 'EventId' => 'EventId', 'Re-SignApptDateText' => 'Re-Sign Appt Date Text', 'Re-signApptTimeText' => 'Re-sign Appt Time Text', 'CompanyName' => 'Company Name');
        $SheetDataKey = array(); 
        foreach ($spreadSheetAry as $dataInSheet) {
            foreach ($dataInSheet as $key => $value) {
                if (in_array(trim($value), $createArray)) {
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
        $totalRecords = ($sheetCount-1); 
        if($totalRecords > 0){
        //loop through the excel data
        for ($i = 1; $i <= $totalRecords; $i ++) {
            $coid = $SheetDataKey['COID'];
            $eventid = $SheetDataKey['EventId'];
            $date = $SheetDataKey['Re-SignApptDateText'];  
            $time = $SheetDataKey['Re-signApptTimeText'];
            $companyname = $SheetDataKey['CompanyName'];

            $company_id = filter_var(trim($spreadSheetAry[$i][$coid]), FILTER_SANITIZE_NUMBER_INT);
            $event_id = filter_var(trim($spreadSheetAry[$i][$eventid]), FILTER_SANITIZE_NUMBER_INT);
            $day = filter_var(trim($spreadSheetAry[$i][$date]), FILTER_SANITIZE_STRING); 
            $time = filter_var(trim($spreadSheetAry[$i][$time]), FILTER_SANITIZE_STRING);
            $company_name = filter_var(trim($spreadSheetAry[$i][$companyname]), FILTER_SANITIZE_STRING);
       
            
            $appointment = new appointment($conn);  
            $stmt = $appointment->addOrUpdateAppointment($event_id, $company_id,  $day, $time, $company_name, $user_id);
           
            if ($stmt->execute()) {


            $query = "SELECT 
                                *
                            FROM
                                appointment
                            WHERE ((company_id = '' OR company_id IS NULL)  OR (day = '' OR day IS NULL)  OR (time = '' OR time IS NULL))";
            if ($event_id != "") {
                    $addCondition = "and event_id = ?";
            }
            if ($company_id != "") {
                $addCondition .= "and company_id = ?";
            }
            $query .= $addCondition;
            $query .= " order by created_date desc";

            // prepare query statement
            $stmt = $conn->prepare($query);
            $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
            if ($company_id != "") {
                $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
            }
            // execute query
            $stmt->execute(); 

            $emptyRecordCount = $stmt->rowCount(); 
            //check if records > 0
            if ($emptyRecordCount > 0) {
                array_push($retArr,$event_id);
            }else{
                       
            }
            $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
                
            } else {
                $missedRowCount++; 
            }
            } 
        $missedRecordCount = count($retArr);
        if($missedRecordCount > 0){
            $eventIdValues = implode(",",$retArr);
            $query = "SELECT 
                                    *
                                FROM
                                    appointment
                                WHERE ((company_id = '' OR company_id IS NULL)  OR (day = '' OR day IS NULL)  OR (time = '' OR time IS NULL))";
                    $addCondition = "and event_id IN ($eventIdValues)";
                $query .= $addCondition;
                $query .= " order by created_date desc";

                                // prepare query statement
                                $stmt = $conn->prepare($query);
                                // execute query
                                $stmt->execute();     //$stmt->debugDumpParams();

                                $emptyRecordCount = $stmt->rowCount(); 
                                //check if records > 0
                                if ($emptyRecordCount > 0) {
                                    $selectBoxDisplay =  "<select id='recenteventselection' name='recenteventselection' style='display:none;'>";
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                            extract($row);
                                            $selectBoxDisplay .= "<option>" . $event_id . "</option>";
                                        }
                                    $selectBoxDisplay .= "</select>";
                                }else{    
                                } 
                            }
        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Re-sign appointment file upload is not complete.</div>';
        }
        //$successmessage = '<div class="alert alert-success">'.$message.'</div>';
        echo json_encode(array('status' => 200, 'error' => $message, 'selectbox' => $selectBoxDisplay,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords )); exit;
        }else{
            $message = '<div class="errorMessage errormsgWrapperDi">Please upload the correct Re-sign appointment file.</div>';
            echo json_encode(array('status' => 401, 'error' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Incorrect File. Please upload the correct Re-sign appointment file.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit; 
        }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid File Type. Upload Re-sign Appointment Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please Upload Re-sign Appointment Excel File.</div>';
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
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx(); 

        // file path
        $spreadSheet = $reader->load($targetPath);
        $excelSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $excelSheet->toArray();


        // array Count
        $sheetCount = count($spreadSheetAry);
        $flag = 0;
        $emptyRecordCount = 0;
        $missedRowCount = 0;
        $retArr = array();
        $selectBoxDisplay = "";
        $missedRecordCount = 0;


        $createArray = array('CoID', 'EventId', 'Exhibiting As', 'Booth Number', 'Company Contact First Name', 'Company Contact Last Name', 'Company Email', 'Hall', 'Floor Manager', 'Phone', 'GES ESE', 'Text Number');
        $makeArray = array( 'CoID' => 'CoID', 'EventId' => 'EventId', 'ExhibitingAs' => 'Exhibiting As', 'BoothNumber' => 'Booth Number', 'CompanyContactFirstName' => 'Company Contact First Name', 'CompanyContactLastName' => 'Company Contact Last Name', 'CompanyEmail' => 'Company Email', 'Hall' => 'Hall', 'FloorManager' => 'Floor Manager', 'Phone' => 'Phone', 'GESESE' => 'GES ESE', 'TextNumber' => 'Text Number');
        $sheetDataKey = array(); 
        foreach ($spreadSheetAry as $dataInSheet) {
            foreach ($dataInSheet as $key => $value) {//echo "<pre>"; print_r(trim($value)); print_r($createArray);
                if (in_array(trim($value), $createArray)) {//echo trim($value);
                    $value = preg_replace('/\s*/', '', $value);
                    $sheetDataKey[trim($value)] = $key;
                } 
            }
        }  
        $dataDiff = array_diff_key($makeArray, $sheetDataKey); //print_r($dataDiff); die("qqq");
        if (empty($dataDiff)) {
            $flag = 1;
        }

        // match excel sheet column
        if ($flag == 1) {
        $totalRecords = ($sheetCount-1);
        if($totalRecords > 0){
        //loop through the excel data
        for ($i = 1; $i <= $totalRecords; $i ++) {
            $coid = $sheetDataKey['CoID'];
            $eventid = $sheetDataKey['EventId'];
            $exhibiting_as = $sheetDataKey['ExhibitingAs'];
            $booth_number = $sheetDataKey['BoothNumber'];
            $company_contact_first_name = $sheetDataKey['CompanyContactFirstName'];
            $company_contact_last_name = $sheetDataKey['CompanyContactLastName'];
            $company_email = $sheetDataKey['CompanyEmail'];
            $hall = $sheetDataKey['Hall'];
            $floor_manager = $sheetDataKey['FloorManager'];
            $phone = $sheetDataKey['Phone'];
            $ges_ese = $sheetDataKey['GESESE'];
            $text_number = $sheetDataKey['TextNumber'];

            $company_id = filter_var(trim($spreadSheetAry[$i][$coid]), FILTER_SANITIZE_NUMBER_INT);
            $event_id = filter_var(trim($spreadSheetAry[$i][$eventid]), FILTER_SANITIZE_NUMBER_INT);
            $company_name = filter_var(trim($spreadSheetAry[$i][$exhibiting_as]), FILTER_SANITIZE_STRING);
            $booth = filter_var(trim($spreadSheetAry[$i][$booth_number]), FILTER_SANITIZE_STRING);
            $company_contact_firstname = filter_var(trim($spreadSheetAry[$i][$company_contact_first_name]), FILTER_SANITIZE_STRING); 
            $company_contact_lastname = filter_var(trim($spreadSheetAry[$i][$company_contact_last_name]), FILTER_SANITIZE_STRING);
            $company_email = filter_var(trim($spreadSheetAry[$i][$company_email]), FILTER_SANITIZE_STRING);
            $hall = filter_var(trim($spreadSheetAry[$i][$hall]), FILTER_SANITIZE_STRING);
            $fm_name = filter_var(trim($spreadSheetAry[$i][$floor_manager]), FILTER_SANITIZE_STRING);
            $fm_phone = filter_var(trim($spreadSheetAry[$i][$phone]), FILTER_SANITIZE_STRING);
            $ges_ese = filter_var(trim($spreadSheetAry[$i][$ges_ese]), FILTER_SANITIZE_STRING);
            $fm_text_number = filter_var(trim($spreadSheetAry[$i][$text_number]), FILTER_SANITIZE_STRING);
       
       
            
            $boothdetails = new boothdetails($conn);  
            $stmt = $boothdetails->addOrUpdateBoothDetails($event_id, $company_id, $company_name, $booth, $company_contact_firstname, $company_contact_lastname, $company_email, $hall, $fm_name, $fm_phone, $ges_ese, $fm_text_number, $user_id);
            
            if ($stmt->execute()) {
                $query = "SELECT 
                                    *
                                FROM
                                    booth_details
                                WHERE ((booth = '' OR booth IS NULL)  OR (hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL))";
                if ($event_id != "") {
                    $addCondition = "and event_id = ?";
                }
                if ($company_id != "") {
                    $addCondition .= "and company_id = ?";
                }
                $query .= $addCondition;
                $query .= " order by created_date desc";

                // prepare query statement
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
                if ($company_id != "") {
                    $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
                }
                // execute query
                $stmt->execute();     //$stmt->debugDumpParams();

                $emptyRecordCount = $stmt->rowCount(); 
                //check if records > 0
                if ($emptyRecordCount > 0) {
                    array_push($retArr,$event_id);
                }else{
                           
                }
                $message = '<div class="alert alert-success">Floor Manager file upload is complete.</div>';
                
            } else {
                $missedRowCount++;
            }
            } 
            $missedRecordCount = count($retArr);
            if($missedRecordCount > 0){
            $eventIdValues = implode(",",$retArr);
            $query = "SELECT 
                                    *
                                FROM
                                    booth_details
                                WHERE ( (booth = '' OR booth IS NULL)  OR (hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL))";
            $addCondition = "and event_id IN ($eventIdValues)";
            $query .= $addCondition;
            $query .= " order by created_date desc";

            // prepare query statement
            $stmt = $conn->prepare($query);
            // execute query
            $stmt->execute();     //$stmt->debugDumpParams();

            $emptyRecordCount = $stmt->rowCount(); 
            //check if records > 0
            if ($emptyRecordCount > 0) {
                $selectBoxDisplay =  "<select id='recenteventselection' name='recenteventselection' style='display:none;'>";
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        extract($row);
                        $selectBoxDisplay .= "<option>" . $event_id . "</option>";
                }
            $selectBoxDisplay .= "</select>";
            }else{    
            } 
        }
        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Floor Manager file upload is not complete.</div>';
        }
        
        echo json_encode(array('status' => 200, 'error' => $message, 'selectbox' => $selectBoxDisplay,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords )); exit;
        }else{
            $message = '<div class="errorMessage errormsgWrapperDi">Please upload the correct Floor Manager file.</div>';
            echo json_encode(array('status' => 401, 'error' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Incorrect File. Please upload the correct Floor Manager file.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit; 
        }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid File Type. Upload Floor Manager Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please Upload Floor Manager Excel File.</div>';
        echo json_encode(array('status' => 401, 'error' => $message)); exit;
    }
}
