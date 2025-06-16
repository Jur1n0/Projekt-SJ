<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $gdpr_consent;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register() {
        $query = "INSERT INTO " . $this->table_name . "
          SET
              first_name = :first_name,
              last_name = :last_name,
              email = :email,
              password = :password,
              gdpr_consent = :gdpr_consent,
              role = 'user',
              created_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->gdpr_consent = (int)$this->gdpr_consent;

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
            error_log("Registration error: " . $e->getMessage());
            return false;
        }
        return false;
    }

    public function login() {
        $query = "SELECT id, first_name, last_name, email, password, role FROM " . $this->table_name . " WHERE email = :email LIMIT 0,1";

        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $stmt->bindParam(':email', $this->email);

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($this->password, $row['password'])) {
                $this->id = $row['id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT id, first_name, last_name, email, role FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT id, first_name, last_name, email, role FROM " . $this->table_name . " ORDER BY first_name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      first_name = :first_name,
                      last_name = :last_name,
                      email = :email
                  WHERE
                      id = :id";

        $stmt = $this->conn->prepare($query);

        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii používateľa: " . $e->getMessage());
            return false;
        }
    }

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

    public function updatePassword() {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }
}
?>