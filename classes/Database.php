<?php
  require_once("AppException.php");
  class Database {
    const SERVER_NAME = "localhost";
    const DB_NAME = "bhglobal";
    const USERNAME = "bhgdb";
    const PASSWORD = "bhglobalindia@123";

    private $conn;
    private $stmt;

    function __construct() {
      $this->conn = $this->get_conn(self::SERVER_NAME, self::USERNAME, self::PASSWORD, self::DB_NAME);
    }

    function __destruct() {
      if($this->conn)
        $this->conn->close();
      if($this->stmt)
        $this->stmt->close();
    }

    function prepare($query) {
      $stmt = $this->conn->prepare($query);
      return $stmt;
    }

    function get_conn($servername, $username, $password, $dbname) {
      $conn = new mysqli($servername, $username, $password, $dbname);
      if($conn->connect_error) {
        throw new AppException("Connection failed to DB: " . $conn->connect_error, AppException::DB_ERROR);
      }
      return $conn;
    }

  }
?>
