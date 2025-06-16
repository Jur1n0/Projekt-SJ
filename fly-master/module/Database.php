<?php
class Database {
    private $host = 'localhost';
    private $port = '3306'; // Skontrolujte si port MySQL. Predvolený je 3306.
    private $username = 'root'; // Skontrolujte vaše DB používateľské meno
    private $password = ''; // Skontrolujte VAŠE HESLO K DATABÁZE! Ak je prázdne, nechajte takto.
    private $database_name = 'database'; // Názov vašej databázy
    private $charset = "utf8mb4";

    private $pdo;

    public function __construct(){
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database_name};port={$this->port};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Chyba pripojenia k databáze: " . $e->getMessage());
            die("Chyba pripojenia k databáze. Prosím, skúste to neskôr alebo kontaktujte administrátora.");
        }
    }

    public function getConnection(){
        return $this->pdo;
    }
}
?>