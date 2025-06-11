<?php
// classes/User.php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $gdpr_consent;
    public $role; // Toto je dôležité, predpokladáme, že tabuľka users má stĺpec 'role'

    public function __construct($db) {
        $this->conn = $db;
    }

    // Funkcia na registráciu nového používateľa
    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      first_name = :first_name,
                      last_name = :last_name,
                      email = :email,
                      password = :password,
                      gdpr_consent = :gdpr_consent,
                      role = 'user'"; // Predvolená rola 'user' pri registrácii

        $stmt = $this->conn->prepare($query);

        // Očistenie a hashovanie dát
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT); // Hashovanie hesla
        $this->gdpr_consent = (int)$this->gdpr_consent;

        // Bindovanie parametrov
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':gdpr_consent', $this->gdpr_consent);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            // Kontrola duplicitného e-mailu
            if ($e->getCode() == 23000) { // SQLSTATE pre duplicitný záznam
                if (strpos($e->getMessage(), 'email') !== false) {
                    throw new Exception("E-mail už existuje.");
                }
            }
            error_log("Chyba registrácie: " . $e->getMessage()); // Zalogovať chybu na serveri
            return false;
        }
        return false;
    }

    // Funkcia na prihlásenie používateľa
    public function login() {
        $query = "SELECT id, first_name, last_name, email, password, role
                  FROM " . $this->table_name . "
                  WHERE email = :email
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Overenie hashovaného hesla
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->email = $row['email'];
                $this->role = $row['role']; // Získa rolu z databázy
                return true;
            }
        }
        return false;
    }

    // Funkcia na získanie používateľa podľa ID (pre CRUD)
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->gdpr_consent = $row['gdpr_consent'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // Funkcia na aktualizáciu používateľských dát (pre CRUD)
    public function update() {
        $query = "UPDATE " . $this->table_name . "
              SET
                  first_name = :first_name,
                  last_name = :last_name,
                  email = :email,
                  gdpr_consent = :gdpr_consent
              WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->gdpr_consent = (int)$this->gdpr_consent;
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':gdpr_consent', $this->gdpr_consent);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            // Kontrola duplicitného e-mailu
            if ($e->getCode() == 23000) { // SQLSTATE pre duplicitný záznam
                if (strpos($e->getMessage(), 'email') !== false) {
                    // Vyhodíme výnimku, ktorú chytíme v process_update_user.php
                    throw new Exception("E-mail '" . $this->email . "' už existuje u iného používateľa.");
                }
            }
            error_log("Chyba pri aktualizácii používateľa: " . $e->getMessage());
            return false;
        }
    }

    public function updateRole() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      role = :role
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Očistiť a overiť, či je rola platná (voliteľné, ale odporúčané)
        $this->role = htmlspecialchars(strip_tags($this->role));
        // Môžete pridať validáciu, napr. if (!in_array($this->role, ['user', 'admin', 'moderator'])) { return false; }
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

    // Funkcia na odstránenie používateľa (pre CRUD)
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        return $stmt->execute();
    }

    // Funkcia na získanie všetkých používateľov (pre admina)
    public function readAll() {
        $query = "SELECT id, first_name, last_name, email, role FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}

?>