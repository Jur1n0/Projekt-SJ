<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password; // Bude obsahovať nehashované heslo pri vstupe, hashované po načítaní z DB
    public $gdpr_consent;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funkcia na registráciu nového používateľa
    public function register() {
        // ... (váš existujúci kód pre registráciu je v poriadku, pokiaľ hashovanie funguje)
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      first_name = :username,
                      last_name = '',
                      email = :email,
                      password = :password,
                      gdpr_consent = :gdpr_consent,
                      role = 'user',
                      created_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT); // Hashovanie hesla pred uložením
        $this->gdpr_consent = (int)$this->gdpr_consent;

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':gdpr_consent', $this->gdpr_consent);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Logovanie chyby, napr. duplicitný e-mail
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    // Pôvodná funkcia login() je nahradená touto vylepšenou verziou
    public function login() {
        // Skúsime nájsť používateľa podľa emailu
        $query = "SELECT id, first_name, email, password, role FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        // Očistenie emailu pred jeho použitím v dotaze
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Používateľ nájdený, teraz overíme heslo
            // $this->password tu obsahuje nehashované heslo z formulára
            // $row['password'] obsahuje hashované heslo z databázy
            if (password_verify($this->password, $row['password'])) {
                // Heslo sa zhoduje, naplníme vlastnosti objektu User
                $this->id = $row['id'];
                $this->username = $row['first_name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                return true; // Prihlásenie úspešné
            }
        }
        return false; // E-mail nenájdený alebo heslo nesprávne
    }

    // Funkcia na prečítanie jedného používateľa (pre dashboad, nie pre login)
    // Túto metódu môžete ponechať, ale pre login ju priamo nevolajte ako overenie.
    public function readOne() {
        $query = "SELECT id, first_name, email, role FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->username = $row['first_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Funkcia na prečítanie všetkých používateľov (pre admin dashboard)
    public function readAll() {
        $query = "SELECT id, first_name, email, role FROM " . $this->table_name . " ORDER BY first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Funkcia na aktualizáciu používateľa (admin)
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      first_name = :username,
                      email = :email
                  WHERE
                      id = :id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii používateľa: " . $e->getMessage());
            return false;
        }
    }

    // Funkcia na aktualizáciu roly (admin)
    public function updateRole() {
        $query = "UPDATE " . $this->table_name . " SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii roly používateľa: " . $e->getMessage());
            return false;
        }
    }

    // Funkcia na zmenu hesla
    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Funkcia na odstránenie používateľa
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }
}
?>