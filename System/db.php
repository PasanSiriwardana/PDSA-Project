<?php
class Database {
    private $host = 'localhost';
    private $port = 3307; // Specify the port number here
    private $db_name = 'your_database_name';
    private $username = 'your_username';
    private $password = 'your_password';
    public $conn;

    public function getConnection() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->db_name,
            $this->port // Specify the port number here
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}
?>
