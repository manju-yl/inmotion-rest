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

    public function __construct($db) {
        $this->conn = $db;
    }

    //get booth details
    function getBoothDetails($data) {
        $event_id = filter_var($data->event_id, FILTER_SANITIZE_NUMBER_INT);
        $company_id = filter_var($data->company_id, FILTER_SANITIZE_NUMBER_INT);
        $booth = filter_var($data->booth, FILTER_SANITIZE_NUMBER_INT);
        $addCondition = "";
        if ($company_id != "" ) {
            $addCondition = "and bd.company_id = ?";
        }
        if ($booth != "" ) {
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
                        e.event_name
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
        $flag=0;
        if ($company_id != "") {
            $stmt->bindParam(2,$company_id);
        } else if ($booth != ""){
            $stmt->bindParam(2,$booth);
            $flag=1;
        }
        if ($flag == 0) {
           if ($booth != ""){
            $stmt->bindParam(3,$booth);
           } 
        }
        // execute query
        $stmt->execute();

        return $stmt;
    }

}
