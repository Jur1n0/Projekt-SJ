<?php
class Database {
    private $host = 'localhost';
    private $port = '3306'; //port pre MySQL
    private $username = 'root';
    private $password = '';
    private $database_name = 'database';
    private $charset = "utf8";

    private $pdo;

    public function __construct(){
        $this->pdo = new PDO(
            "mysql:host={$this->host};
                    dbname={$this->database_name};
                    port={$this->port};
                    charset={$this->charset}",
            $this->username,
            $this->password
        );
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE,  PDO::ERRMODE_EXCEPTION);
    }

    public function __destruct(){
        $this->pdo = null;
    }

    public function getConnection(){
        return $this->pdo;
    }
}


?>