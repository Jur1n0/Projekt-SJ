<?php
// module/Database.php
class Database {
    private $host = 'localhost';
    private $port = '3306';
    private $username = 'root';
    private $password = ''; // Uistite sa, že toto je správne heslo pre vášho MySQL root používateľa
    private $database_name = 'database'; // Názov vašej databázy
    private $charset = "utf8mb4"; // Doporucujem utf8mb4 pre sirsiu podporu znakov

    private $pdo;

    public function __construct(){
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database_name};port={$this->port};charset={$this->charset}";
            $this->pdo = new PDO($dsn, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Chyba pripojenia k databáze: " . $e->getMessage());
        }
    }

    public function getConnection(){
        return $this->pdo;
    }

    /**
     * Metóda na vytvorenie nového používateľa v tabuľke 'users'.
     * Heslo sa musí zahashovať PRED odovzdaním tejto metóde.
     * Predpokladáme, že rola je 'user' pre bežných používateľov pri registrácii.
     */
    public function createUser($first_name, $last_name, $email, $hashed_password, $gdpr_consent) {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (first_name, last_name, email, password, gdpr_consent, role) VALUES (?, ?, ?, ?, ?, 'user')");
            return $stmt->execute([$first_name, $last_name, $email, $hashed_password, $gdpr_consent]);
        } catch (PDOException $e) {
            error_log("Chyba pri vytváraní používateľa: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Získa používateľa z tabuľky 'users' podľa e-mailu.
     * Táto metóda je používaná v User.php triede na overenie.
     * Zahrnuté pre úplnosť, aj keď getUserByEmail logika je v User.php.
     */
    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
?>