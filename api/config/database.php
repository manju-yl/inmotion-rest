<?php
require '../vendor/autoload.php';

// used to get mysql database connection
class DatabaseService {

    private $connection;

    public function getConnection() {

        $this->connection = null; 

        try {
            $this->connection = new PDO("mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
        } catch (PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
        }

        return $this->connection;
    }

}
