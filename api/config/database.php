<?php
require '../vendor/autoload.php';

// used to get mysql database connection
class DatabaseService {

    private $connection;

    public function getConnection() {

        $this->connection = null; 

        try {
            $this->connection = new PDO("mysql:host=" . $_SERVER['DB_HOST'] . ";dbname=" . $_SERVER['DB_DATABASE'], $_SERVER['DB_USERNAME'], $_SERVER['DB_PASSWORD']);
        } catch (PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }

        return $this->connection;
    }

}
