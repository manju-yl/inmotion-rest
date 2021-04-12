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

    //get user details
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
                        a.event_id = ? ";
        $query .= $addCondition;

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($event_id)));
        if ($company_id != "") {
            $stmt->bindParam(2,htmlspecialchars(strip_tags($company_id)));
        } else if ($company_email != ""){
            $stmt->bindParam(2,strip_tags($company_email));
        }
        if ($company_email != ""){
            $stmt->bindParam(3,strip_tags($company_email));
        }
        // execute query
        $stmt->execute();

        return $stmt;
    }

}
