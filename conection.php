<?php

require "config/config.php";

ini_set('display_errors', 1);

class Connection {
  private static $instance = null;
  private $conn;

  private function __construct() {
    try {
      $this->conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
      $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      die("Conexión fallida: " . $e->getMessage());
    }
  }

  public static function getInstance(): self {
    if (self::$instance === null) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function getConnection() {
    return $this->conn;
  }
}