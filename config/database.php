<?php
class Database {
    private $host = "localhost";
    private $port = "3307";  // Added port configuration
    private $db_name = "nexus_notes";
    private $username = "root";
    private $password = "";  // Empty password for default XAMPP setup
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Database Connection Error: " . $e->getMessage() . "<br>";
            echo "Details:<br>";
            echo "- Host: " . $this->host . "<br>";
            echo "- Port: " . $this->port . "<br>";
            echo "- Database: " . $this->db_name . "<br>";
            echo "- Username: " . $this->username . "<br>";
            echo "Please check your MySQL credentials and make sure the database exists.";
            die();
        }

        return $this->conn;
    }
}
?> 