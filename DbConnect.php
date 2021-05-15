<?php

class DbConnect {

  private $pdo;
  private $dsn = "mysql:host=localhost;dbname=project;charset=utf8";
  private $options = [
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  ];

  public function connectToDatabase(): PDO
  {
    try {
      $this->pdo = new PDO($this->dsn, "root", "", $this->options);
    } catch (Exception $e) {
      error_log($e->getMessage());
      exit ('Something went wrong.');
    }

    return $this->pdo;
  }

}

