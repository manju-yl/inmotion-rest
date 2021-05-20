<?php

class Appointment {

    private $conn;
    private $table_name = "appointment";
    // properties
    public $co_id;
    public $company_name;
    public $event_id;
    public $event_name;
    public $day;
    public $time;
    public $company_contact_first_name;
    public $company_contact_last_name;
    public $company_email;

    public function __construct($db) {
        $this->conn = $db;
    }

    //get appointment details
    function getAppointments($data) {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $company_id = filter_var($data->company_id, FILTER_SANITIZE_NUMBER_INT);
        $company_email = filter_var($data->company_email, FILTER_SANITIZE_EMAIL);
        $addCondition = "";
        if ($company_id != "") {
            $addCondition = "and a.company_id = ?";
        }
        if ($company_email != "") {
            $addCondition .= " and c.company_email = ?";
        }
        // select appointments based on event_id and company_id
        $query = "SELECT 
        c.co_id,
        c.company_name,
        e.event_id,
        e.event_name,
        a.day,
        a.time,
        c.company_contact_first_name,
        c.company_contact_last_name,
        c.company_email
        FROM
        " . $this->table_name . " a
        LEFT JOIN
        company c ON a.company_id = c.co_id
        LEFT JOIN
        event e ON e.event_id = a.event_id
        WHERE
        a.event_id = ? ";
        $query .= $addCondition;

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        $flag = 0;
        if ($company_id != "") {
            $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
        } else if ($company_email != "") {
            $stmt->bindParam(2, strip_tags($company_email));
            $flag = 1;
        }
        if ($flag == 0) {
            if ($company_email != "") {
                $stmt->bindParam(3, strip_tags($company_email));
            }
        }
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //Add OR Update Appointment details
    function addOrUpdateAppointment($event_id, $company_id,  $day, $time, $company_name, $user_id) {
        if($event_id != "" && $company_id != ""){
            $query = "SELECT 
            c.co_id,
            c.company_name,
            e.event_id,
            e.event_name,
            a.day,
            a.time,
            c.company_contact_first_name,
            c.company_contact_last_name,
            c.company_email
            FROM
            " . $this->table_name . " a
            LEFT JOIN
            company c ON a.company_id = c.co_id
            LEFT JOIN
            event e ON e.event_id = a.event_id
            WHERE
            a.event_id = ? and a.company_id = ?";

            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
            $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));

            // execute query
            $stmt->execute();
            $num = $stmt->rowCount(); 
            if ($num > 0) {
                $updatequery = "update company
                SET company_name = :company_name,
                created_by = '$user_id', created_date=now() where co_id = :company_id";
                $stmt = $this->conn->prepare($updatequery);

                $stmt->bindParam(':company_name', $company_name);
                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));

                // execute query
                $stmt->execute();

                $updateAppointment = "
                UPDATE " . $this->table_name . "
                SET day = :day,
                time = :time,
                created_by='$user_id', created_date=now() where event_id = :event_id and company_id = :company_id";

                // prepare query statement
                $stmt = $this->conn->prepare($updateAppointment); 

                $stmt->bindParam(':day', $day);
                $stmt->bindParam(':time', $time);
                $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));

            }else{
                $query = "SELECT event_id FROM event WHERE event_id = ? LIMIT 0,1";

                // prepare query statement
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));

                // execute query
                $stmt->execute(); 
                $num = $stmt->rowCount();  
                if ($num > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $event_id = $row['event_id'];
                    $checkCompanyExists = "SELECT co_id FROM company WHERE co_id = ? LIMIT 0,1";

                    // prepare query statement
                    $companyExists = $this->conn->prepare($checkCompanyExists);
                    $companyExists->bindParam(1, htmlspecialchars(strip_tags($company_id)));

                    // execute query
                    $companyExists->execute(); 
                    $companyCount = $companyExists->rowCount(); 
                    if ($companyCount > 0) {
                        $row = $companyExists->fetch(PDO::FETCH_ASSOC);
                        $updatequery = "update company
                        SET company_name = :company_name,
                        created_by = '$user_id', created_date=now() where co_id = :company_id;
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        day = :day,
                        time = :time,
                        created_by='$user_id', created_date=now()";

                        // prepare query statement
                        $stmt = $this->conn->prepare($updatequery);

                        $stmt->bindParam(':company_name', $company_name);
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':day', $day);
                        $stmt->bindParam(':time', $time);


                    }else{
                        $query = "
                        INSERT INTO company
                        SET co_id = :company_id,
                        company_name = :company_name,
                        created_by = '$user_id', created_date=now();
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        day = :day,
                        time = :time,
                        created_by='$user_id', created_date=now()"; 

                        // prepare query statement
                        $stmt = $this->conn->prepare($query); 

                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':company_name', $company_name);
                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':day', $day);
                        $stmt->bindParam(':time', $time);
                    }

                }else{
                    $query = "SELECT co_id FROM company WHERE co_id = ? LIMIT 0,1";

                    // prepare query statement
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(1, htmlspecialchars(strip_tags($company_id)));

                    // execute query
                    $stmt->execute(); 
                    $num = $stmt->rowCount(); 
                    if ($num > 0) {
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $updatequery = "update company
                        SET company_name = :company_name,
                        created_by = '$user_id', created_date=now() where co_id = :company_id";
                        $stmt = $this->conn->prepare($updatequery);

                        $stmt->bindParam(':company_name', $company_name);
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));

                        // execute query
                        $stmt->execute();

                        $insertquery = "
                        INSERT INTO event
                        SET event_id = :event_id, created_date=now();
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        day = :day,
                        time = :time,
                        created_by='$user_id', created_date=now()";

                        // prepare query statement
                        $stmt = $this->conn->prepare($insertquery); 
                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':day', $day);
                        $stmt->bindParam(':time', $time);
                    }else{
                        $query = "
                        INSERT INTO event
                        SET event_id = :event_id, created_date=now();
                        INSERT INTO company
                        SET co_id = :company_id,
                        company_name = :company_name,
                        created_by = '$user_id', created_date=now();
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        day = :day,
                        time = :time,
                        created_by='$user_id', created_date=now()";

                        // prepare query statement
                        $stmt = $this->conn->prepare($query); 

                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':company_name', $company_name);
                        $stmt->bindParam(':day', $day);
                        $stmt->bindParam(':time', $time);

                    }
                }
            }
        }else{
            $query = ""; 

            // prepare query statement
            $stmt = $this->conn->prepare($query); 
        }
        return $stmt;
    }


    //get appointments having empty records
    function getEmptyAppointmentDetails() {
        $query = "SELECT 
        DISTINCT event_id, MAX(created_date)
        FROM
        " . $this->table_name . " 
        WHERE ((day = '' OR day IS NULL)  OR (time = '' OR time IS NULL)) ";
        $query .= "GROUP BY event_id  order by MAX(created_date) desc";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //download particular events having empty records in appointment details
    function downloadAppointmentDetails($event_id) {
        // select all query
        $query = "SELECT 
        c.co_id,
        c.company_name,
        e.event_id,
        e.event_name,
        a.day,
        a.time,
        c.company_contact_first_name,
        c.company_contact_last_name,
        c.company_email
        FROM
        " . $this->table_name . " a
        LEFT JOIN
        company c ON a.company_id = c.co_id
        LEFT JOIN
        event e ON e.event_id = a.event_id
        WHERE
        ((a.company_id = '' OR a.company_id IS NULL)  OR (a.day = '' OR a.day IS NULL)  OR (a.time = '' OR a.time IS NULL)) and a.event_id = ? ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        // execute query
        $stmt->execute();
        return $stmt;
    }

    //get appointments having empty records based on eventid and company_id
    function getEmptyAppointmentOnEventDetails($event_id, $company_id) {
        $addCondition = "";
        $query = "SELECT 
        event_id
        FROM
        " . $this->table_name . "
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
        $stmt = $this->conn->prepare($query); 
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        if ($company_id != "") {
            $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
        }
        // execute query
        $stmt->execute(); 

        return $stmt;
    }

    //store uploaded file details to database
    function insertOrUpdateUploadedFile($path, $type) {
        $query = "SELECT 
        filename, type
        FROM
        uploads
        WHERE filename='$path' and type='$type'"; 
        // prepare query statement
        $stmt = $this->conn->prepare($query); 
        // execute query
        $stmt->execute(); 

        $num = $stmt->rowCount(); 
        if ($num > 0) {
            $updatequery = "update uploads
                        SET filename = '$path',
                        type = '$type', created_date=now() where filename = '$path' and type = '$type'";
            $stmt = $this->conn->prepare($updatequery);
            // execute query
            $stmt->execute(); 
        }else{
            $query = "INSERT INTO uploads
                        SET filename = '$path', type = '$type', created_date=now()";
            $stmt = $this->conn->prepare($query);
            // execute query
            $stmt->execute(); 
        }
    }
    
    function deleteEventData($data) {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $query = "DELETE
        FROM
        " . $this->table_name . "
        WHERE
        event_id = ? ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        // execute query
        return $stmt->execute();

    }
    
    function checkIfEventExists($data) {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT *
        FROM
        " . $this->table_name . "
        WHERE
        event_id = ? ";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        // execute query
        $stmt->execute();
        // execute query
        return $stmt;
    }




}
