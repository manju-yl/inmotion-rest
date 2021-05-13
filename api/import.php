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
        $reader = new Xlsx();
        $spreadSheet = $reader->load($_FILES['file']['tmp_name']);

        $loadedSheetNames = $spreadSheet->getSheetNames(); 

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadSheet);

        if (!file_exists('uploads')) {
            mkdir('./uploads', 0777, true);
        }
        $file = $_FILES['file']['name'];
        $info = pathinfo($file);
        $fileName =  $info['filename'];

        $targetPath = 'uploads/' . $fileName.'.csv';
        foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            $writer->setSheetIndex($sheetIndex); 
            $writer->save($targetPath);
        } 
        $appointment = new appointment($conn); 
        $appointment->insertOrUpdateUploadedFile($targetPath, 'appointment');
        
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        // file path
        $spreadSheet = $reader->load($targetPath);
        $csvSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $csvSheet->toArray(); 

        // array Count
        $sheetCount = count($spreadSheetAry); 
        if($sheetCount > 1) {
        $flag = 0;
        $emptyRecordCount = 0;
        $missedRowCount = 0;
        $missedRecordCount = 0;
        $emptyUniqueAppointment = 0;
        $addCondition = "";
        $appointmentArr = array();
        $uniqueEventIdDisp = "";
        $eventIdArray = array();
        $eventCount = 0;


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
            break;
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
            $day = trim($spreadSheetAry[$i][$date]); 
            $time = trim($spreadSheetAry[$i][$time]);
            $company_name = trim($spreadSheetAry[$i][$companyname]);
            
            //get appointment object
            $appointment = new appointment($conn);  
            $stmt = $appointment->addOrUpdateAppointment($event_id, $company_id,  $day, $time, $company_name, $user_id); 
           
            if ($stmt->execute()) {//$stmt->debugDumpParams();
                $query = "SELECT 
                                        event_id
                                    FROM
                                        appointment
                                    WHERE ( (day = '' OR day IS NULL)  OR (time = '' OR time IS NULL))"; 
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
                $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
                // execute query
                $stmt->execute(); //$stmt->debugDumpParams();
                //$stmt = $appointment->getEmptyAppointmentOnEventDetails($event_id, $company_id);

                $emptyRecordCount = $stmt->rowCount(); 
                //check if records > 0
                if ($emptyRecordCount > 0) {
                    $appointmentItem = array(
                        "eventId" => $event_id,
                        "companyId" => $company_id
                    );
                    
                    array_push($appointmentArr, $appointmentItem);

                }else{     
                }
                
                array_push($eventIdArray,$event_id); 
                
            } else {
                $missedRowCount++; 
            }
            }
        $eventIdUnique = count(array_unique($eventIdArray)); 
        //to get missed record count
        $missedAppointmentRecordCount = count($appointmentArr);
        if($missedAppointmentRecordCount > 0){
            $missedRecordUniqueArray = array_unique($appointmentArr, SORT_REGULAR);
            $tempArr = array_unique(array_column($appointmentArr, 'eventId'));
            $uniqueAppointmentCount = count($missedRecordUniqueArray);
            $eventCount = count($tempArr);
            //print_r($missedRecordUniqueArray);
                foreach($missedRecordUniqueArray as $key => $value){
                    $query = "SELECT 
                                        event_id
                                    FROM
                                        appointment
                                    WHERE ( (day = '' OR day IS NULL)  OR (time = '' OR time IS NULL))"; 
                    $addCondition = "and event_id = ?";
                    $addCondition .= "and company_id = ?";
                    $query .= $addCondition;
                    $query .= " order by created_date desc";
                    // prepare query statement
                    $stmt = $conn->prepare($query); 
                    $stmt->bindParam(1, htmlspecialchars(strip_tags($value[eventId])));
                    $stmt->bindParam(2, htmlspecialchars(strip_tags($value[companyId])));
                    // execute query
                    $stmt->execute(); //$stmt->debugDumpParams();

                    $emptyCount = $stmt->rowCount(); 
                    if($emptyCount > 0){
                        $missedRecordCount++;
                    }
                }
                
                if($missedRecordCount > 0){
                    if($eventIdUnique == 1){
                        $dispEventId = array_unique($eventIdArray); 
                        $message = '<div class="alert alert-success">Re-sign appointment file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                        $uniqueEventIdDisp = $dispEventId[0];

                    }else{
                        $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
                    }
                    $emptyUniqueAppointment = $missedRecordCount;
                }else{
                    if($eventIdUnique == 1){
                        $dispEventId = array_unique($eventIdArray); 
                        $message = '<div class="alert alert-success">Re-sign appointment file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                        $uniqueEventIdDisp = $dispEventId[0];

                    }else{
                        $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
                    }
                    $emptyUniqueAppointment = $missedRecordCount;  
                }
        }else{
            if($eventIdUnique == 1){
                $dispEventId = array_unique($eventIdArray); 
                $message = '<div class="alert alert-success">Re-sign appointment file upload is complete for Event ID: '. $dispEventId[0] .'</div>';
                $uniqueEventIdDisp = $dispEventId[0];
            }else{
                $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
            }
        }

        if($totalRecords > 0){
          if($missedRowCount > 0){
           if($eventIdUnique == 1){
                $dispEventId = array_unique($eventIdArray); 
                $message = '<div class="alert alert-success">Re-sign appointment file upload is complete for Event ID: '. $dispEventId[0] .'</div>';
                $uniqueEventIdDisp = $dispEventId[0];
            }else{
                $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
            }
          }
        }

        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Re-sign appointment file upload is not complete.</div>';
        }
        echo json_encode(array('status' => 200, 'message' => $message,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords, 'emptyUniqueAppointment' =>  $emptyUniqueAppointment, 'uniqueEventIdDisp' => $uniqueEventIdDisp, 'eventCount' => $eventCount)); exit;
        }else{
            $message = '<div class="errorMessage errormsgWrapperDi">Please upload the correct re-sign appointment file.</div>';
            echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Incorrect column names. Please upload the correct re-sign appointment file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Please check the file uploaded, there is no data to import.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid file type. Upload re-sign appointment excel file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please upload re-sign appointment excel file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit;
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
        $reader = new Xlsx();
        $spreadSheet = $reader->load($_FILES['myfile']['tmp_name']);

        $loadedSheetNames = $spreadSheet->getSheetNames(); 

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadSheet);

        if (!file_exists('uploads')) {
            mkdir('./uploads', 0777, true);
        }
        $file = $_FILES['myfile']['name'];
        $info = pathinfo($file);
        $fileName =  $info['filename'];

        $targetPath = 'uploads/' . $fileName.'.csv';
        foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
            $writer->setSheetIndex($sheetIndex); 
            $writer->save($targetPath);
        } 
        $appointment = new appointment($conn); 
        $appointment->insertOrUpdateUploadedFile($targetPath, 'floormanager');

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        // file path
        $spreadSheet = $reader->load($targetPath);
        $csvSheet = $spreadSheet->getActiveSheet();
        $spreadSheetAry = $csvSheet->toArray();

        // array Count
        $sheetCount = count($spreadSheetAry);
        if($sheetCount > 1) {
        $flag = 0;
        $emptyRecordCount = 0;
        $missedRowCount = 0;
        $floorArr = array();
        $missedRecordCount = 0;
        $emptyUniqueFloor = 0;
        $addCondition = "";
        $uniqueEventIdDisp = "";
        $eventIdArray = array();
        $eventCount = 0;


        $createArray = array('CoID', 'EventId', 'Exhibiting As', 'Booth Number', 'Company Contact First Name', 'Company Contact Last Name', 'Company Email', 'Hall', 'Floor Manager', 'Phone', 'GES ESE', 'Text Number');
        $makeArray = array( 'CoID' => 'CoID', 'EventId' => 'EventId', 'ExhibitingAs' => 'Exhibiting As', 'BoothNumber' => 'Booth Number', 'CompanyContactFirstName' => 'Company Contact First Name', 'CompanyContactLastName' => 'Company Contact Last Name', 'CompanyEmail' => 'Company Email', 'Hall' => 'Hall', 'FloorManager' => 'Floor Manager', 'Phone' => 'Phone', 'GESESE' => 'GES ESE', 'TextNumber' => 'Text Number');
        $sheetDataKey = array(); 
        foreach ($spreadSheetAry as $dataInSheet) {
            foreach ($dataInSheet as $key => $value) {
                if (in_array(trim($value), $createArray)) {
                    $value = preg_replace('/\s*/', '', $value);
                    $sheetDataKey[trim($value)] = $key;
                } 
            }
            break;
        } 
        $dataDiff = array_diff_key($makeArray, $sheetDataKey); 
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
            $first_name = $sheetDataKey['CompanyContactFirstName'];
            $last_name = $sheetDataKey['CompanyContactLastName'];
            $company_email = $sheetDataKey['CompanyEmail'];
            $hall = $sheetDataKey['Hall'];
            $floor_manager = $sheetDataKey['FloorManager'];
            $phone = $sheetDataKey['Phone'];
            $ges_ese = $sheetDataKey['GESESE'];
            $text_number = $sheetDataKey['TextNumber'];

            $company_id = filter_var(trim($spreadSheetAry[$i][$coid]), FILTER_SANITIZE_NUMBER_INT);
            $event_id = filter_var(trim($spreadSheetAry[$i][$eventid]), FILTER_SANITIZE_NUMBER_INT);
            $company_name = trim($spreadSheetAry[$i][$exhibiting_as]);
            $booth = trim($spreadSheetAry[$i][$booth_number]);
            $first_name = trim($spreadSheetAry[$i][$first_name]);
            $last_name = trim($spreadSheetAry[$i][$last_name]); 
            $company_email = trim($spreadSheetAry[$i][$company_email]);
            $hall = trim($spreadSheetAry[$i][$hall]);
            $fm_name = trim($spreadSheetAry[$i][$floor_manager]);
            $fm_phone = trim($spreadSheetAry[$i][$phone]);
            $ges_ese = trim($spreadSheetAry[$i][$ges_ese]);
            $fm_text_number = trim($spreadSheetAry[$i][$text_number]);
       
            //get booth object
            $boothdetails = new boothdetails($conn);  
            $stmt = $boothdetails->addOrUpdateBoothDetails($event_id, $company_id, $booth, $hall, $fm_name, $fm_phone, $ges_ese, $fm_text_number, $company_name, $first_name, $last_name, $company_email, $user_id);
            
            if ($stmt->execute()) {//$stmt->debugDumpParams();
                //$stmt = $boothdetails->getEmptyFloorOnEventDetails($event_id, $company_id);
                $query = "SELECT 
                                    event_id
                                FROM
                                    booth_details
                                WHERE ((hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL))";
                $addCondition = "and event_id = ?";
                $addCondition .= "and company_id = ?";
                $query .= $addCondition;
                $query .= " order by created_date desc";

                // prepare query statement
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
                $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
                // execute query
                $stmt->execute(); 

                $emptyRecordCount = $stmt->rowCount(); 
                //check if records > 0
                if ($emptyRecordCount > 0) {
                    $floorItem = array(
                    "eventId" => $event_id,
                    "companyId" => $company_id
                    );
                    
                    array_push($floorArr, $floorItem);
                }else{
                }

                array_push($eventIdArray,$event_id);
                
            } else {
                $missedRowCount++;
            }
            } 

        $eventIdUnique = count(array_unique($eventIdArray));
        //to get missed record count
        $missedFloorRecordCount = count($floorArr); 
        if($missedFloorRecordCount > 0){
            $missedRecordUniqueArray = array_unique($floorArr, SORT_REGULAR);
            $tempArr = array_unique(array_column($floorArr, 'eventId')); 
            $eventCount = count($tempArr);
            foreach($missedRecordUniqueArray as $key => $value){
                    $query = "SELECT 
                                    event_id
                                FROM
                                    booth_details
                                WHERE ((hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL))";
                $addCondition = "and event_id = ?";
                $addCondition .= "and company_id = ?";
                $query .= $addCondition;
                $query .= " order by created_date desc";

                // prepare query statement
                $stmt = $conn->prepare($query);
                $stmt->bindParam(1, htmlspecialchars(strip_tags($value[eventId])));
                $stmt->bindParam(2, htmlspecialchars(strip_tags($value[companyId])));
                // execute query
                $stmt->execute(); 

                $emptyCount = $stmt->rowCount(); 
                if($emptyCount > 0){
                    $missedRecordCount++;
                }
                }
                
                if($missedRecordCount > 0){
                    if($eventIdUnique == 1){
                        $dispEventId = array_unique($eventIdArray); 
                        $message = '<div class="alert alert-success">Floor manager file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                        $uniqueEventIdDisp = $dispEventId[0];

                    }else{
                        $message = '<div class="alert alert-success">Floor manager file upload is complete.</div>';
                    }
                    $emptyUniqueFloor = $missedRecordCount;
                }else{
                    if($eventIdUnique == 1){
                        $dispEventId = array_unique($eventIdArray); 
                        $message = '<div class="alert alert-success">Floor manager file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                        $uniqueEventIdDisp = $dispEventId[0];

                    }else{
                        $message = '<div class="alert alert-success">Floor manager file upload is complete.</div>';
                    }
                    $emptyUniqueFloor = $missedRecordCount;
                }
        }else{
            if($eventIdUnique == 1){
                $dispEventId = array_unique($eventIdArray); 
                $message = '<div class="alert alert-success">Floor manager file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                $uniqueEventIdDisp = $dispEventId[0];
            }else{
                $message = '<div class="alert alert-success">Floor manager file upload is complete.</div>';
            }
        }

        if($totalRecords > 0){
          if($missedRowCount > 0){
           if($eventIdUnique == 1){
                $dispEventId = array_unique($eventIdArray); 
                $message = '<div class="alert alert-success">Floor manager file upload is complete for EventId: '. $dispEventId[0] .'</div>';
                $uniqueEventIdDisp = $dispEventId[0];
            }else{
                $message = '<div class="alert alert-success">Floor manager file upload is complete.</div>';
            }
          }
        }

        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Floor manager file upload is not complete.</div>';
        }
        
        echo json_encode(array('status' => 200, 'message' => $message,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords, 'emptyUniqueFloor' =>  $emptyUniqueFloor, 'uniqueEventIdDisp' => $uniqueEventIdDisp, 'eventCount' => $eventCount )); exit;
        }else{
            $message = '<div class="errorMessage errormsgWrapperDi">Please upload the correct floor manager file.</div>';
            echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Incorrect column names. Please upload the correct floor manager file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
        }else{
           $message = '<div class="errorMessage errormsgWrapperDi">Please check the file uploaded, there is no data to import.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit; 
        }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Invalid file type. Upload floor manager excel file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit;
    }
    } else {
        $message = '<div class="errorMessage errormsgWrapperDi">Please upload floor manager excel file.</div>';
        echo json_encode(array('status' => 401, 'message' => $message)); exit;
    }
}
