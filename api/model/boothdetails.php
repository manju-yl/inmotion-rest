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
        $booth = filter_var($data->booth, FILTER_SANITIZE_NUMBER_INT);
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
        bd.ges_ese
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

    //Add OR Update Booth details
    function addOrUpdateBoothDetails($event_id, $company_id, $company_name, $booth, $company_contact_first_name, $company_contact_last_name, $company_email, $hall, $fm_name, $fm_phone, $ges_ese, $fm_text_number, $user_id) {
        if($event_id != "" && $company_id != "" && $booth != ""){
            $addCondition = "";
	    // select booths based on event_id and company_id and booth
            $query = "SELECT 
            c.co_id,
            e.event_id,
            bd.booth,
            bd.hall,
            bd.fm_name,
            bd.fm_text_number,
            c.company_name,
            e.event_name,
            bd.ges_ese
            FROM
            " . $this->table_name . " bd
            LEFT JOIN
            company c ON bd.company_id = c.co_id
            LEFT JOIN
            event e ON e.event_id = bd.event_id
            WHERE
            bd.event_id = ? and bd.company_id= ? and bd.booth = ?";
            $query .= $addCondition;

            // prepare query statement
            $stmt = $this->conn->prepare($query);
            $event_id = htmlspecialchars(strip_tags($event_id));
            $company_id = htmlspecialchars(strip_tags($company_id));
            $booth = htmlspecialchars(strip_tags($booth));

            $stmt->bindParam(1, $event_id);
            $stmt->bindParam(2, $company_id);
            $stmt->bindParam(3, $booth);
            // execute query
            $stmt->execute();  
            $num = $stmt->rowCount(); 
            if($num > 0){
                $updatequery = "update company
                SET company_name = :company_name,
                company_contact_first_name = :company_contact_first_name,
                company_contact_last_name = :company_contact_last_name,
                company_email = :company_email,
                created_by = '$user_id', created_date=now() where co_id = :company_id";

                // prepare query statement
                $stmt = $this->conn->prepare($updatequery);

                $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                $stmt->bindParam(':company_name', htmlspecialchars(strip_tags($company_name)));
                $stmt->bindParam(':company_contact_first_name', htmlspecialchars(strip_tags($company_contact_first_name)));
                $stmt->bindParam(':company_contact_last_name', htmlspecialchars(strip_tags($company_contact_last_name)));
                $stmt->bindParam(':company_email', htmlspecialchars(strip_tags($company_email)));
                // execute query
                $stmt->execute();

                $updateBooth = "
                UPDATE " . $this->table_name . "
                SET hall = :hall,
                fm_name= :fm_name,
                fm_phone= :fm_phone,
                fm_text_number= :fm_text_number,
                ges_ese= :ges_ese, created_by = '$user_id', created_date=now() where event_id = :event_id and company_id = :company_id and booth = :booth"; 

                // prepare query statement
                $stmt = $this->conn->prepare($updateBooth); 

                $stmt->bindParam(':booth', htmlspecialchars(strip_tags($booth)));
                $stmt->bindParam(':hall', htmlspecialchars(strip_tags($hall)));
                $stmt->bindParam(':fm_name', htmlspecialchars(strip_tags($fm_name)));
                $stmt->bindParam(':fm_phone', htmlspecialchars(strip_tags($fm_phone)));
                $stmt->bindParam(':fm_text_number', htmlspecialchars(strip_tags($fm_text_number)));
                $stmt->bindParam(':ges_ese', htmlspecialchars(strip_tags($ges_ese)));
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
                        company_contact_first_name = :company_contact_first_name,
                        company_contact_last_name = :company_contact_last_name,
                        company_email = :company_email,
                        created_by = '$user_id', created_date=now() where co_id = :company_id;
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
                        $stmt = $this->conn->prepare($updatequery);

                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':company_name', htmlspecialchars(strip_tags($company_name)));
                        $stmt->bindParam(':company_contact_first_name', htmlspecialchars(strip_tags($company_contact_first_name)));
                        $stmt->bindParam(':company_contact_last_name', htmlspecialchars(strip_tags($company_contact_last_name)));
                        $stmt->bindParam(':company_email', htmlspecialchars(strip_tags($company_email)));
                        $stmt->bindParam(':booth', htmlspecialchars(strip_tags($booth)));
                        $stmt->bindParam(':hall', htmlspecialchars(strip_tags($hall)));
                        $stmt->bindParam(':fm_name', htmlspecialchars(strip_tags($fm_name)));
                        $stmt->bindParam(':fm_phone', htmlspecialchars(strip_tags($fm_phone)));
                        $stmt->bindParam(':fm_text_number', htmlspecialchars(strip_tags($fm_text_number)));
                        $stmt->bindParam(':ges_ese', htmlspecialchars(strip_tags($ges_ese)));
                        
                    }else{
                        $query = "
                        INSERT INTO company
                        SET co_id = :company_id,
                        company_name = :company_name,
                        company_contact_first_name = :company_contact_first_name,
                        company_contact_last_name = :company_contact_last_name,
                        company_email = :company_email,
                        created_by = '$user_id', created_date=now();
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

                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':company_name', htmlspecialchars(strip_tags($company_name)));
                        $stmt->bindParam(':company_contact_first_name', htmlspecialchars(strip_tags($company_contact_first_name)));
                        $stmt->bindParam(':company_contact_last_name', htmlspecialchars(strip_tags($company_contact_last_name)));
                        $stmt->bindParam(':company_email', htmlspecialchars(strip_tags($company_email)));
                        $stmt->bindParam(':booth', htmlspecialchars(strip_tags($booth)));
                        $stmt->bindParam(':hall', htmlspecialchars(strip_tags($hall)));
                        $stmt->bindParam(':fm_name', htmlspecialchars(strip_tags($fm_name)));
                        $stmt->bindParam(':fm_phone', htmlspecialchars(strip_tags($fm_phone)));
                        $stmt->bindParam(':fm_text_number', htmlspecialchars(strip_tags($fm_text_number)));
                        $stmt->bindParam(':ges_ese', htmlspecialchars(strip_tags($ges_ese)));

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
                        company_contact_first_name = :company_contact_first_name,
                        company_contact_last_name = :company_contact_last_name,
                        company_email = :company_email,
                        created_by = '$user_id', created_date=now() where co_id = :company_id";
                        $stmt = $this->conn->prepare($updatequery);

                        $stmt->bindParam(':company_name', htmlspecialchars(strip_tags($company_name)));
                        $stmt->bindParam(':company_contact_first_name', htmlspecialchars(strip_tags($company_contact_first_name)));
                        $stmt->bindParam(':company_contact_last_name', htmlspecialchars(strip_tags($company_contact_last_name)));
                        $stmt->bindParam(':company_email', htmlspecialchars(strip_tags($company_email)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        // execute query
                        $stmt->execute(); 

                        $insertquery = "
                        INSERT INTO event
                        SET event_id = :event_id, created_date=now();
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        booth = :booth,
                        hall = :hall,
                        fm_name= :fm_name,
                        fm_phone= :fm_phone,
                        fm_text_number= :fm_text_number,
                        ges_ese= :ges_ese,
                        created_by = '$user_id', created_date=now()";
                        // prepare query statement
                        $stmt = $this->conn->prepare($insertquery); 
                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':booth', htmlspecialchars(strip_tags($booth)));
                        $stmt->bindParam(':hall', htmlspecialchars(strip_tags($hall)));
                        $stmt->bindParam(':fm_name', htmlspecialchars(strip_tags($fm_name)));
                        $stmt->bindParam(':fm_phone', htmlspecialchars(strip_tags($fm_phone)));
                        $stmt->bindParam(':fm_text_number', htmlspecialchars(strip_tags($fm_text_number)));
                        $stmt->bindParam(':ges_ese', htmlspecialchars(strip_tags($ges_ese)));
                    }else{
                        $query = "
                        INSERT INTO event
                        SET event_id = :event_id, created_date=now();
                        INSERT INTO company
                        SET co_id = :company_id,
                        company_name = :company_name,
                        company_contact_first_name = :company_contact_first_name,
                        company_contact_last_name = :company_contact_last_name,
                        company_email = :company_email,
                        created_by = '$user_id', created_date=now();
                        INSERT INTO " . $this->table_name . "
                        SET company_id = :company_id,
                        event_id = :event_id,
                        booth = :booth,
                        hall = :hall,
                        fm_name= :fm_name,
                        fm_phone= :fm_phone,
                        fm_text_number= :fm_text_number,
                        ges_ese= :ges_ese,
                        created_by = '$user_id', created_date=now()";
                        // prepare query statement
                        $stmt = $this->conn->prepare($query); 

                        $stmt->bindParam(':event_id', htmlspecialchars(strip_tags($event_id)));
                        $stmt->bindParam(':company_id', htmlspecialchars(strip_tags($company_id)));
                        $stmt->bindParam(':company_name', htmlspecialchars(strip_tags($company_name)));
                        $stmt->bindParam(':company_contact_first_name', htmlspecialchars(strip_tags($company_contact_first_name)));
                        $stmt->bindParam(':company_contact_last_name', htmlspecialchars(strip_tags($company_contact_last_name)));
                        $stmt->bindParam(':company_email', htmlspecialchars(strip_tags($company_email)));
                        $stmt->bindParam(':booth', htmlspecialchars(strip_tags($booth)));
                        $stmt->bindParam(':hall', htmlspecialchars(strip_tags($hall)));
                        $stmt->bindParam(':fm_name', htmlspecialchars(strip_tags($fm_name)));
                        $stmt->bindParam(':fm_phone', htmlspecialchars(strip_tags($fm_phone)));
                        $stmt->bindParam(':fm_text_number', htmlspecialchars(strip_tags($fm_text_number)));
                        $stmt->bindParam(':ges_ese', htmlspecialchars(strip_tags($ges_ese)));
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

    //get floor manager having empty records based on eventid and company_id
    function getEmptyFloorOnEventDetails($event_id, $company_id) {
        $addCondition = "";
        $query = "SELECT 
                                        *
        FROM
        " . $this->table_name . "
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
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        if ($company_id != "") {
            $stmt->bindParam(2, htmlspecialchars(strip_tags($company_id)));
        }
            // execute query
            $stmt->execute();     //$stmt->debugDumpParams();


            return $stmt;
        }

}
