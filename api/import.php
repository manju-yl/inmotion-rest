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
        $finalmultiple = $appointment->batchImport($totalRecords); 
        for ($i = 1; $i <= $totalRecords; $i ++) {
            $coid = $SheetDataKey['COID'];
            $eventid = $SheetDataKey['EventId'];
            $date = $SheetDataKey['Re-SignApptDateText'];  
            $time = $SheetDataKey['Re-signApptTimeText'];
            $companyname = $SheetDataKey['CompanyName'];

            $company_id = filter_var(trim($spreadSheetAry[$i][$coid]), FILTER_SANITIZE_NUMBER_INT);
            $event_id = filter_var(trim($spreadSheetAry[$i][$eventid]), FILTER_SANITIZE_NUMBER_INT);
            $day = filter_var(addslashes(trim($spreadSheetAry[$i][$date]))); 
            $time = filter_var(addslashes(trim($spreadSheetAry[$i][$time])));
            $company_name = filter_var(addslashes(trim($spreadSheetAry[$i][$companyname])));

            if(empty($company_id) || empty($event_id)){
                $missedRowCount++;
            }else{
                $query = "SELECT 
                c.co_id,
                e.event_id
                FROM
                appointment a
                LEFT JOIN
                company c ON a.company_id = c.co_id
                LEFT JOIN
                event e ON e.event_id = a.event_id
                WHERE
                a.event_id = '$event_id' and a.company_id = '$company_id'";

                // prepare query statement
                $stmt = $conn->prepare($query);

                // execute query
                $stmt->execute(); 
                $num = $stmt->rowCount(); 
                if ($num > 0) {
                    $updateAppointment .= "
                    UPDATE company
                    SET company_name = '$company_name',
                    created_by = '$user_id', created_date=now() where co_id = '$company_id';
                    UPDATE appointment
                    SET day = '$day',
                    time = '$time',
                    created_by='$user_id', created_date=now() where event_id = '$event_id' and company_id = '$company_id';";

                    $stmt = $conn->prepare($updateAppointment);
                    if ($i%1000 == 0) {
                        $stmt->execute(); 
                        $updateAppointment = "";
                    }elseif ($i > $finalmultiple){
                        $stmt->execute(); 
                        $updateAppointment = "";
                    }

                }else{

                    $appointmentquery .= "INSERT INTO event (event_id) VALUES('$event_id')
                    ON DUPLICATE KEY UPDATE event_id= '$event_id', created_date= now();
                    INSERT INTO company (co_id, company_name, created_by) VALUES('$company_id', '$company_name', '$user_id')
                    ON DUPLICATE KEY UPDATE co_id= '$company_id', company_name = '$company_name', created_date= now(), created_by='$user_id';
                    INSERT INTO appointment
                            SET company_id = '$company_id',
                            event_id = '$event_id',
                            day = '$day',
                            time = '$time',
                            created_by='$user_id', created_date=now();"; 

                    $stmt = $conn->prepare($appointmentquery);
                    if ($i%1000 == 0) {
                        $stmt->execute(); 
                        $appointmentquery = "";
                    }elseif ($i > $finalmultiple){
                        $stmt->execute(); 
                        $appointmentquery = "";
                    }

                }
                $appointmentItem = array(
                    "eventId" => $event_id,
                    "companyId" => $company_id
                );
                        
                array_push($appointmentArr, $appointmentItem);
                    
                array_push($eventIdArray,$event_id); 

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
                foreach($missedRecordUniqueArray as $key => $value){
                    $query = "SELECT 
                                        event_id
                                    FROM
                                        appointment
                                    WHERE ( (day = '' OR day IS NULL)  OR (time = '' OR time IS NULL))"; 
                    $addCondition = "and event_id = '$value[eventId]'";
                    $addCondition .= "and company_id = '$value[companyId]'";
                    $query .= $addCondition;
                    $query .= " order by created_date desc";
                    // prepare query statement
                    $stmt = $conn->prepare($query);
                    // execute query
                    $stmt->execute(); 

                    $emptyCount = $stmt->rowCount(); 
                    if($emptyCount > 0){
                        $missedRecordCount++;
                    }
                }
        }
        $eventIdUnique = count(array_unique($eventIdArray));
        if($eventIdUnique == 1){
            $dispEventId = array_unique($eventIdArray); 
            $message = '<div class="alert alert-success">Re-sign appointment file upload is complete for Event ID: '. $dispEventId[0] .'</div>';
            $uniqueEventIdDisp = $dispEventId[0];
        }else{
            $message = '<div class="alert alert-success">Re-sign appointment file upload is complete.</div>';
        }
        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Re-sign appointment file upload is not complete.</div>';
        }
        echo json_encode(array('status' => 200, 'message' => $message,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords, 'eventCount' => $eventCount)); exit;
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
        //get booth object
        $boothdetails = new boothdetails($conn); 
        $totalRecords = ($sheetCount-1);
        if($totalRecords > 0){
        $finalmultiple = $appointment->batchImport($totalRecords); 
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
            $company_name = filter_var(addslashes(trim($spreadSheetAry[$i][$exhibiting_as])));
            $booth = filter_var(addslashes(trim($spreadSheetAry[$i][$booth_number])));
            $first_name = filter_var(addslashes(trim($spreadSheetAry[$i][$first_name])));
            $last_name = filter_var(addslashes(trim($spreadSheetAry[$i][$last_name]))); 
            $company_email = filter_var(addslashes(trim($spreadSheetAry[$i][$company_email])));
            $hall = filter_var(addslashes(trim($spreadSheetAry[$i][$hall])));
            $fm_name = filter_var(addslashes(trim($spreadSheetAry[$i][$floor_manager])));
            $fm_phone = filter_var(addslashes(trim($spreadSheetAry[$i][$phone])));
            $ges_ese = filter_var(addslashes(trim($spreadSheetAry[$i][$ges_ese])));
            $fm_text_number = filter_var(addslashes(trim($spreadSheetAry[$i][$text_number])));
       
            if(empty($company_id) || empty($event_id) || empty($booth)){
                $missedRowCount++;
            }else{
                // select booths based on event_id and company_id and booth
                $query = "SELECT 
                c.co_id,
                e.event_id,
                bd.booth
                FROM
                booth_details bd
                LEFT JOIN
                company c ON bd.company_id = c.co_id
                LEFT JOIN
                event e ON e.event_id = bd.event_id
                WHERE
                bd.event_id = '$event_id' and bd.company_id= '$company_id' and bd.booth = '$booth'";

                // prepare query statement
                $stmt = $conn->prepare($query);
                $event_id = htmlspecialchars(strip_tags($event_id));
                $company_id = htmlspecialchars(strip_tags($company_id));

                // execute query
                $stmt->execute();  
                $num = $stmt->rowCount(); 
                if($num > 0){
                    $updateBooth .= "
                    UPDATE company
                    SET company_name = '$company_name',
                    company_contact_first_name = '$first_name',
                    company_contact_last_name = '$last_name',
                    company_email = '$company_email',
                    created_by = '$user_id', created_date=now() where co_id = '$company_id';
                    UPDATE booth_details
                    SET hall = '$hall',
                    fm_name= '$fm_name',
                    fm_phone= '$fm_phone',
                    fm_text_number= '$fm_text_number',
                    ges_ese= '$ges_ese', created_by = '$user_id', created_date=now() where event_id = '$event_id' and company_id = '$company_id' and booth = '$booth';"; 

                    // prepare query statement
                    $stmt = $conn->prepare($updateBooth);
                    if ($i%1000 == 0) {
                        $stmt->execute(); 
                        $updateBooth = "";
                    }elseif ($i > $finalmultiple){
                        $stmt->execute(); 
                        $updateBooth = "";
                    }

                }else{
                    $boothquery .= "INSERT INTO event (event_id) VALUES('$event_id')
                    ON DUPLICATE KEY UPDATE event_id= '$event_id', created_date= now();
                    INSERT INTO company (co_id, company_name, company_contact_first_name, company_contact_last_name, company_email, created_by) VALUES('$company_id', '$company_name', '$first_name', '$last_name', '$company_email', '$user_id')
                    ON DUPLICATE KEY UPDATE co_id= '$company_id', company_name = '$company_name', company_contact_first_name = '$first_name', company_contact_last_name = '$last_name', company_email = '$company_email', created_by = '$user_id', created_date= now();
                    INSERT INTO booth_details
                    SET company_id = '$company_id',
                    event_id = '$event_id',
                    booth = '$booth',
                    hall = '$hall',
                    fm_name= '$fm_name',
                    fm_phone= '$fm_phone',
                    fm_text_number= '$fm_text_number',
                    ges_ese= '$ges_ese', created_by = '$user_id', created_date=now();"; 

                    // prepare query statement
                    $stmt = $conn->prepare($boothquery); 
                    if ($i%1000 == 0) {
                        $stmt->execute(); 
                        $boothquery = "";
                    }elseif ($i > $finalmultiple){
                        $stmt->execute(); 
                        $boothquery = "";
                    }

                }
                $floorItem = array(
                    "eventId" => $event_id,
                    "companyId" => $company_id
                    );
                    
                array_push($floorArr, $floorItem);

                array_push($eventIdArray,$event_id);

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
                $addCondition = "and event_id = '$value[eventId]'";
                $addCondition .= "and company_id = '$value[companyId]'";
                $query .= $addCondition;
                $query .= " order by created_date desc";

                // prepare query statement
                $stmt = $conn->prepare($query);
                // execute query
                $stmt->execute(); 

                $emptyCount = $stmt->rowCount(); 
                if($emptyCount > 0){
                    $missedRecordCount++;
                }

                }
                
           }
           if($eventIdUnique == 1){
                $dispEventId = array_unique($eventIdArray); 
                $message = '<div class="alert alert-success">Floor manager file upload is complete for EventId: '. $dispEventId[0] .'</div>';
            }else{
                $message = '<div class="alert alert-success">Floor manager file upload is complete.</div>';
            }

        if($totalRecords == $missedRowCount){
            $message = '<div class="errorMessage errormsgWrapperDi">Floor manager file upload is not complete.</div>';
        }
        
        echo json_encode(array('status' => 200, 'message' => $message,'emptyRowsCount' => $missedRecordCount,'missedRowCount' => $missedRowCount, 'totalRecords' => $totalRecords, 'eventCount' => $eventCount )); exit;
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
