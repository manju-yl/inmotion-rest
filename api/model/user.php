<?php

class User {

    // database connection and table name
    private $conn;
    private $table_name = "Users";
    // object properties
    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;

    // constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    //get user details
    function getUser($email) {

        // select all query
        $query = "SELECT id, first_name, last_name, password FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        // prepare query statement
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, htmlspecialchars(strip_tags($email)));
        // execute query
        $stmt->execute();

        return $stmt;
    }

    //get user details
    function addUser($firstName, $lastName, $email, $password) {

        $query = "INSERT INTO " . $this->table_name . "
                SET first_name = :firstname,
                    last_name = :lastname,
                    email = :email,
                    password = :password";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':firstname', htmlspecialchars(strip_tags($firstName)));
        $stmt->bindParam(':lastname', htmlspecialchars(strip_tags($lastName)));
        $stmt->bindParam(':email', htmlspecialchars(strip_tags($email)));

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':password', $password_hash);

        return $stmt;
    }

}
