<?php
class News {
    private $conn;
    private $table_name = "news";

    public $idNews;
    public $Nadpis;
    public $Text;
    public $Obrazok;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      Nadpis = :Nadpis,
                      Text = :Text,
                      Obrazok = :Obrazok";

        $stmt = $this->conn->prepare($query);

        $this->Nadpis = htmlspecialchars(strip_tags($this->Nadpis));
        $this->Text = htmlspecialchars(strip_tags($this->Text));
        $this->Obrazok = htmlspecialchars(strip_tags($this->Obrazok));

        $stmt->bindParam(':Nadpis', $this->Nadpis);
        $stmt->bindParam(':Text', $this->Text);
        $stmt->bindParam(':Obrazok', $this->Obrazok);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readAll() {
        $query = "SELECT idNews, Nadpis, Text, Obrazok, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        try {
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log("Chyba pri čítaní všetkých noviniek: " . $e->getMessage());
            return false;
        }
    }

    public function readOne() {
        $query = "SELECT idNews, Nadpis, Text, Obrazok, created_at FROM " . $this->table_name . " WHERE idNews = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idNews);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->Nadpis = $row['Nadpis'];
            $this->Text = $row['Text'];
            $this->Obrazok = $row['Obrazok'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      Nadpis = :Nadpis,
                      Text = :Text,
                      Obrazok = :Obrazok,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE
                      idNews = :idNews";

        $stmt = $this->conn->prepare($query);

        $this->Nadpis = htmlspecialchars(strip_tags($this->Nadpis));
        $this->Text = htmlspecialchars(strip_tags($this->Text));
        $this->Obrazok = htmlspecialchars(strip_tags($this->Obrazok));
        $this->idNews = htmlspecialchars(strip_tags($this->idNews));

        $stmt->bindParam(':Nadpis', $this->Nadpis);
        $stmt->bindParam(':Text', $this->Text);
        $stmt->bindParam(':Obrazok', $this->Obrazok);
        $stmt->bindParam(':idNews', $this->idNews);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE idNews = ?";

        $stmt = $this->conn->prepare($query);

        $this->idNews = htmlspecialchars(strip_tags($this->idNews));

        $stmt->bindParam(1, $this->idNews);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>