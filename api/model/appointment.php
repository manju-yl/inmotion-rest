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

    //add or update appointment details
    function addOrUpdateAppointment($event_id, $company_id,  $day, $time, $company_name, $user_id) {
        if($event_id != "" && $company_id != ""){
            $query = "SELECT 
            c.co_id,
            e.event_id
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
                $updateAppointment = "
                update company
                SET company_name = :company_name,
                created_by = '$user_id', created_date=now() where co_id = :company_id;
                UPDATE " . $this->table_name . "
                SET day = :day,
                time = :time,
                created_by='$user_id', created_date=now() where event_id = :event_id and company_id = :company_id";

                // prepare query statement
                $stmt = $this->conn->prepare($updateAppointment); 

                $stmt->bindParam(':company_name', $company_name);
                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                $stmt->bindParam(':day', $day);
                $stmt->bindParam(':time', $time);
                $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                $stmt->execute();  echo $stmt->debugDumpParams();

            }else{
                $query = "INSERT INTO event (event_id) VALUES(:event_id)
                ON DUPLICATE KEY UPDATE event_id= :event_id, created_date= now();
                INSERT INTO company (co_id, company_name, created_by) VALUES(:company_id, :company_name, '$user_id')
                ON DUPLICATE KEY UPDATE co_id= :company_id, company_name = :company_name, created_date= now(), created_by='$user_id';
                INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        day = :day,
                        time = :time,
                        created_by='$user_id', 
                        created_date=now()"; 

                // prepare query statement
                $stmt = $this->conn->prepare($query); 

                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                $stmt->bindParam(':company_name', $company_name);
                $stmt->bindParam(':day', $day);
                $stmt->bindParam(':time', $time);
                $stmt->execute();  echo $stmt->debugDumpParams();
            }
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
    
    //to delete event
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
    
    //function to check if event exists or not
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

    //get all floor and appointment event details
    function getAllEventDetails() {
        $query = "SELECT e.event_id
                    FROM event e
                    LEFT OUTER JOIN appointment p 
                     ON p.event_id = e.event_id
                    LEFT OUTER JOIN booth_details s 
                      ON s.event_id=e.event_id 
                      where (
                        p.event_id IS NOT NULL
                        OR
                        s.event_id IS NOT NULL
                      )
                    GROUP BY e.event_id";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //get all floor and appointment company details
    function getAllCompanyDetails($data = "") {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $addCondition = "";
        if ($event_id != "") {
            $addCondition = "and 
                       (
                        p.event_id = '$event_id'
                        OR
                        s.event_id = '$event_id'
                      )";
        }
        $query = "SELECT c.co_id
                    FROM company c
                    LEFT OUTER JOIN appointment p 
                     ON p.company_id = c.co_id
                    LEFT OUTER JOIN booth_details s 
                      ON s.company_id=c.co_id 
                      where (
                        p.company_id IS NOT NULL
                        OR
                        s.company_id IS NOT NULL
                      )";
        $query .= $addCondition;
        $query .= "GROUP BY c.co_id";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    function batchImport($totalcount){
        for ($i=1; $i<=$totalcount; $i++) {
           if ($i%1000 == 0) {
               $finalmultiple = $i;
           }
        }
        return $finalmultiple;
    }


}
