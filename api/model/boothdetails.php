<?php

class BoothDetails {

    private $conn;
    private $table_name = "booth_details";
    // properties
    public $co_id;
    public $company_name;
    public $event_id;
    public $event_name;
    public $booth;
    public $hall;
    public $fm_name;
    public $fm_text_number;
    public $ges_ese;

    public function __construct($db) {
        $this->conn = $db;
    }

    //get booth details
    function getBoothDetails($data) {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $company_id = filter_var($data->company_id, FILTER_SANITIZE_NUMBER_INT);
        $booth = filter_var($data->booth, FILTER_SANITIZE_STRING);
        $addCondition = "";
        if ($company_id != "") {
            $addCondition = "and bd.company_id = ?";
        }
        if ($booth != "") {
            $addCondition .= " and bd.booth = ?";
        }
        // select all query
        $query = "SELECT 
        c.co_id,
        e.event_id,
        bd.booth,
        bd.hall,
        bd.fm_name,
        bd.fm_text_number,
        c.company_name,
        e.event_name,
        bd.ges_ese,
        bd.fm_phone
        FROM
        " . $this->table_name . " bd
        LEFT JOIN
        company c ON bd.company_id = c.co_id
        LEFT JOIN
        event e ON e.event_id = bd.event_id
        WHERE
        bd.event_id = ?";
        $query .= $addCondition;

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $event_id = htmlspecialchars(strip_tags($event_id));
        $company_id = htmlspecialchars(strip_tags($company_id));
        $stmt->bindParam(1, $event_id);
        $flag = 0;
        if ($company_id != "") {
            $stmt->bindParam(2, $company_id);
        } else if ($booth != "") {
            $stmt->bindParam(2, $booth);
            $flag = 1;
        }
        if ($flag == 0) {
            if ($booth != "") {
                $stmt->bindParam(3, $booth);
            }
        }
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //Add or Update Booth details
    function addOrUpdateBoothDetails($event_id, $company_id, $booth, $hall, $fm_name, $fm_phone, $ges_ese, $fm_text_number, $company_name, $company_contact_first_name, $company_contact_last_name, $company_email, $user_id) {
        if($event_id != "" && $company_id != "" && $booth != ""){
            // select booths based on event_id and company_id and booth
            $query = "SELECT 
            c.co_id,
            e.event_id,
            bd.booth
            FROM
            " . $this->table_name . " bd
            LEFT JOIN
            company c ON bd.company_id = c.co_id
            LEFT JOIN
            event e ON e.event_id = bd.event_id
            WHERE
            bd.event_id = ? and bd.company_id= ? and bd.booth = ?";

            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $event_id = htmlspecialchars(strip_tags($event_id));
            $company_id = htmlspecialchars(strip_tags($company_id));

            $stmt->bindParam(1, $event_id);
            $stmt->bindParam(2, $company_id);
            $stmt->bindParam(3, $booth);
            // execute query
            $stmt->execute();
            $num = $stmt->rowCount(); 
            if($num > 0){
                $updateBooth = "UPDATE company
                SET company_name = :company_name,
                company_contact_first_name = :company_contact_first_name,
                company_contact_last_name = :company_contact_last_name,
                company_email = :company_email,
                created_by = '$user_id', created_date=now() where co_id = :company_id;
                UPDATE " . $this->table_name . "
                SET hall = :hall,
                fm_name= :fm_name,
                fm_phone= :fm_phone,
                fm_text_number= :fm_text_number,
                ges_ese= :ges_ese, created_by = '$user_id', created_date=now() where event_id = :event_id and company_id = :company_id and booth = :booth"; 

                // prepare query statement
                $stmt = $this->conn->prepare($updateBooth); 

                $stmt->bindParam(':company_name', $company_name);
                $stmt->bindParam(':company_contact_first_name', $company_contact_first_name);
                $stmt->bindParam(':company_contact_last_name', $company_contact_last_name);
                $stmt->bindParam(':company_email', $company_email);
                $stmt->bindParam(':hall', $hall);
                $stmt->bindParam(':fm_name', $fm_name);
                $stmt->bindParam(':fm_phone', $fm_phone);
                $stmt->bindParam(':fm_text_number', $fm_text_number);
                $stmt->bindParam(':ges_ese', $ges_ese);
                $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                $stmt->bindParam(':booth', $booth);

                $stmt->execute();

            }else{
                $query = "INSERT INTO event (event_id) VALUES(:event_id)
                ON DUPLICATE KEY UPDATE event_id= :event_id, created_date= now();
                INSERT INTO company (co_id, company_name, company_contact_first_name, company_contact_last_name, company_email, created_by) VALUES(:company_id, :company_name, :company_contact_first_name, :company_contact_last_name, :company_email, '$user_id')
                ON DUPLICATE KEY UPDATE co_id= :company_id, company_name = :company_name, company_contact_first_name = :company_contact_first_name, company_contact_last_name = :company_contact_last_name, company_email = :company_email, created_by = '$user_id', created_date= now();
                INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        booth = :booth,
                        hall = :hall,
                        fm_name= :fm_name,
                        fm_phone= :fm_phone,
                        fm_text_number= :fm_text_number,
                        ges_ese= :ges_ese, created_by = '$user_id', created_date=now()"; 

                // prepare query statement
                $stmt = $this->conn->prepare($query); 



                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                $stmt->bindParam(':company_name', $company_name);
                $stmt->bindParam(':company_contact_first_name', $company_contact_first_name);
                $stmt->bindParam(':company_contact_last_name', $company_contact_last_name);
                $stmt->bindParam(':company_email', $company_email);
                $stmt->bindParam(':booth', $booth);
                $stmt->bindParam(':hall', $hall);
                $stmt->bindParam(':fm_name', $fm_name);
                $stmt->bindParam(':fm_phone', $fm_phone);
                $stmt->bindParam(':fm_text_number', $fm_text_number);
                $stmt->bindParam(':ges_ese', $ges_ese);

                $stmt->execute();
                }
            }
        return $stmt;
    }

    //get empty booth details
    function getEmptyBoothDetails() {
        $query = "SELECT 
        DISTINCT event_id, MAX(created_date)
        FROM
        " . $this->table_name . " 
        WHERE ( (booth = '' OR booth IS NULL)  OR (hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL)) ";
        $query .= "GROUP BY event_id  order by MAX(created_date) desc";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //download particular events having empty records in booth details
    function downloadBoothDetails($event_id) {
        // select all query
        $query = "SELECT 
        c.co_id,
        e.event_id,
        bd.booth,
        bd.hall,
        bd.fm_name,
        bd.fm_phone,
        bd.fm_text_number,
        c.company_name,
        c.company_contact_first_name,
        c.company_contact_last_name,
        c.company_email,
        e.event_name,
        bd.ges_ese
        FROM
        " . $this->table_name . " bd
        LEFT JOIN
        company c ON bd.company_id = c.co_id
        LEFT JOIN
        event e ON e.event_id = bd.event_id
        WHERE
        ((booth = '' OR booth IS NULL)  OR (hall = '' OR hall IS NULL) OR (fm_name = '' OR fm_name IS NULL) OR (fm_phone = '' OR fm_phone IS NULL) OR (fm_text_number = '' OR fm_text_number IS NULL) OR (ges_ese = '' OR ges_ese IS NULL)) and bd.event_id = ?";
        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        // execute query
        $stmt->execute(); 
        return $stmt;
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
    
    //to check if event exist or not
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

        return $stmt;
    }



}
