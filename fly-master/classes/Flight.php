<?php

class Flight {
    private $conn;
    private $table_name = "flights";
    private $sales_table_name = "sales"; // Pridajte tabuľku sales

    // Vlastnosti objektu Flight
    public $id;
    public $lietadlo;
    public $miesto_odletu;
    public $miesto_priletu;
    public $datum_cas_odletu;
    public $datum_cas_priletu;
    public $cena;
    public $kapacita_lietadla;
    public $dlzka_letu_hodiny;
    public $dlzka_letu_minuty;
    public $obrazok;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Metóda na pridanie nového letu
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      lietadlo = :lietadlo,
                      miesto_odletu = :miesto_odletu,
                      miesto_priletu = :miesto_priletu,
                      datum_cas_odletu = :datum_cas_odletu,
                      datum_cas_priletu = :datum_cas_priletu,
                      cena = :cena,
                      kapacita_lietadla = :kapacita_lietadla,
                      dlzka_letu_hodiny = :dlzka_letu_hodiny,
                      dlzka_letu_minuty = :dlzka_letu_minuty,
                      obrazok = :obrazok,
                      created_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $this->lietadlo = htmlspecialchars(strip_tags($this->lietadlo));
        $this->miesto_odletu = htmlspecialchars(strip_tags($this->miesto_odletu));
        $this->miesto_priletu = htmlspecialchars(strip_tags($this->miesto_priletu));
        $this->datum_cas_odletu = htmlspecialchars(strip_tags($this->datum_cas_odletu));
        $this->datum_cas_priletu = htmlspecialchars(strip_tags($this->datum_cas_priletu));
        $this->cena = htmlspecialchars(strip_tags($this->cena));
        $this->kapacita_lietadla = htmlspecialchars(strip_tags($this->kapacita_lietadla));
        $this->dlzka_letu_hodiny = htmlspecialchars(strip_tags($this->dlzka_letu_hodiny));
        $this->dlzka_letu_minuty = htmlspecialchars(strip_tags($this->dlzka_letu_minuty));
        $this->obrazok = htmlspecialchars(strip_tags($this->obrazok));

        $stmt->bindParam(":lietadlo", $this->lietadlo);
        $stmt->bindParam(":miesto_odletu", $this->miesto_odletu);
        $stmt->bindParam(":miesto_priletu", $this->miesto_priletu);
        $stmt->bindParam(":datum_cas_odletu", $this->datum_cas_odletu);
        $stmt->bindParam(":datum_cas_priletu", $this->datum_cas_priletu);
        $stmt->bindParam(":cena", $this->cena);
        $stmt->bindParam(":kapacita_lietadla", $this->kapacita_lietadla);
        $stmt->bindParam(":dlzka_letu_hodiny", $this->dlzka_letu_hodiny);
        $stmt->bindParam(":dlzka_letu_minuty", $this->dlzka_letu_minuty);
        $stmt->bindParam(":obrazok", $this->obrazok);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri vytváraní letu: " . $e->getMessage());
            return false;
        }
    }

    // Metóda na prečítanie jedného letu
    public function readOne() {
        $query = "SELECT
                      f.id, f.lietadlo, f.miesto_odletu, f.miesto_priletu, f.datum_cas_odletu,
                      f.datum_cas_priletu, f.cena, f.kapacita_lietadla, f.dlzka_letu_hodiny,
                      f.dlzka_letu_minuty, f.obrazok, f.created_at, f.updated_at
                  FROM
                      " . $this->table_name . " f
                  WHERE
                      f.id = ?
                  LIMIT
                      0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->lietadlo = $row['lietadlo'];
            $this->miesto_odletu = $row['miesto_odletu'];
            $this->miesto_priletu = $row['miesto_priletu'];
            $this->datum_cas_odletu = $row['datum_cas_odletu'];
            $this->datum_cas_priletu = $row['datum_cas_priletu'];
            $this->cena = $row['cena'];
            $this->kapacita_lietadla = $row['kapacita_lietadla'];
            $this->dlzka_letu_hodiny = $row['dlzka_letu_hodiny'];
            $this->dlzka_letu_minuty = $row['dlzka_letu_minuty'];
            $this->obrazok = $row['obrazok'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Metóda na aktualizáciu letu
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      lietadlo = :lietadlo,
                      miesto_odletu = :miesto_odletu,
                      miesto_priletu = :miesto_priletu,
                      datum_cas_odletu = :datum_cas_odletu,
                      datum_cas_priletu = :datum_cas_priletu,
                      cena = :cena,
                      kapacita_lietadla = :kapacita_lietadla,
                      dlzka_letu_hodiny = :dlzka_letu_hodiny,
                      dlzka_letu_minuty = :dlzka_letu_minuty,
                      obrazok = :obrazok,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE
                      id = :id";

        $stmt = $this->conn->prepare($query);

        $this->lietadlo = htmlspecialchars(strip_tags($this->lietadlo));
        $this->miesto_odletu = htmlspecialchars(strip_tags($this->miesto_odletu));
        $this->miesto_priletu = htmlspecialchars(strip_tags($this->miesto_priletu));
        $this->datum_cas_odletu = htmlspecialchars(strip_tags($this->datum_cas_odletu));
        $this->datum_cas_priletu = htmlspecialchars(strip_tags($this->datum_cas_priletu));
        $this->cena = htmlspecialchars(strip_tags($this->cena));
        $this->kapacita_lietadla = htmlspecialchars(strip_tags($this->kapacita_lietadla));
        $this->dlzka_letu_hodiny = htmlspecialchars(strip_tags($this->dlzka_letu_hodiny));
        $this->dlzka_letu_minuty = htmlspecialchars(strip_tags($this->dlzka_letu_minuty));
        $this->obrazok = htmlspecialchars(strip_tags($this->obrazok));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':lietadlo', $this->lietadlo);
        $stmt->bindParam(':miesto_odletu', $this->miesto_odletu);
        $stmt->bindParam(':miesto_priletu', $this->miesto_priletu);
        $stmt->bindParam(':datum_cas_odletu', $this->datum_cas_odletu);
        $stmt->bindParam(':datum_cas_priletu', $this->datum_cas_priletu);
        $stmt->bindParam(':cena', $this->cena);
        $stmt->bindParam(':kapacita_lietadla', $this->kapacita_lietadla);
        $stmt->bindParam(':dlzka_letu_hodiny', $this->dlzka_letu_hodiny);
        $stmt->bindParam(':dlzka_letu_minuty', $this->dlzka_letu_minuty);
        $stmt->bindParam(':obrazok', $this->obrazok);
        $stmt->bindParam(':id', $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii letu: " . $e->getMessage());
            return false;
        }
    }

    // Metóda na vymazanie letu
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri mazaní letu: " . $e->getMessage());
            return false;
        }
    }

    // Metóda na čítanie všetkých letov s filtrovaním a triedením
    public function readFilteredAndSorted(
        $search_query = '', $miesto_odletu = '', $miesto_priletu = '',
        $min_capacity = null, $max_capacity = null,
        $min_price = null, $max_price = null,
        $date_from = '', $date_to = '',
        $sort_by = 'datum_cas_odletu', $sort_order = 'ASC'
    ) {
        $query = "SELECT f.*, COALESCE(COUNT(s.flight_id), 0) AS order_count
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->sales_table_name . " s ON f.id = s.flight_id "; // JOIN pre popularitu

        $conditions = [];
        $params = [];

        if (!empty($search_query)) {
            $conditions[] = "(f.lietadlo LIKE :search_query OR f.miesto_odletu LIKE :search_query OR f.miesto_priletu LIKE :search_query)";
            $params[':search_query'] = '%' . $search_query . '%';
        }
        if (!empty($miesto_odletu)) {
            $conditions[] = "f.miesto_odletu LIKE :miesto_odletu";
            $params[':miesto_odletu'] = '%' . $miesto_odletu . '%';
        }
        if (!empty($miesto_priletu)) {
            $conditions[] = "f.miesto_priletu LIKE :miesto_priletu";
            $params[':miesto_priletu'] = '%' . $miesto_priletu . '%';
        }
        if ($min_capacity !== null) {
            $conditions[] = "f.kapacita_lietadla >= :min_capacity";
            $params[':min_capacity'] = $min_capacity;
        }
        if ($max_capacity !== null) {
            $conditions[] = "f.kapacita_lietadla <= :max_capacity";
            $params[':max_capacity'] = $max_capacity;
        }
        if ($min_price !== null) {
            $conditions[] = "f.cena >= :min_price";
            $params[':min_price'] = $min_price;
        }
        if ($max_price !== null) {
            $conditions[] = "f.cena <= :max_price";
            $params[':max_price'] = $max_price;
        }
        if (!empty($date_from)) {
            $conditions[] = "DATE(f.datum_cas_odletu) >= :date_from";
            $params[':date_from'] = $date_from;
        }
        if (!empty($date_to)) {
            $conditions[] = "DATE(f.datum_cas_odletu) <= :date_to";
            $params[':date_to'] = $date_to;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY f.id "; // Group by flight to count sales

        // Overenie a nastavenie poradia stĺpcov pre ORDER BY
        $allowed_sort_columns = ['datum_cas_odletu', 'cena', 'lietadlo', 'miesto_odletu', 'order_count']; // Pridajte order_count
        if (!in_array($sort_by, $allowed_sort_columns)) {
            $sort_by = 'datum_cas_odletu'; // Predvolené, ak je neplatné
        }

        $sort_order = strtoupper($sort_order);
        if ($sort_order !== 'ASC' && $sort_order !== 'DESC') {
            $sort_order = 'ASC'; // Predvolené, ak je neplatné
        }

        $query .= " ORDER BY " . $sort_by . " " . $sort_order; // Odstránenie "f." pred $sort_by

        $stmt = $this->conn->prepare($query);

        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }

        $stmt->execute();
        return $stmt;
    }

    public function readPopularFlights($limit = 4) {
        $query = "SELECT f.*, COUNT(s.flight_id) AS order_count
                  FROM " . $this->table_name . " f
                  LEFT JOIN " . $this->sales_table_name . " s ON f.id = s.flight_id
                  GROUP BY f.id
                  ORDER BY order_count DESC, f.datum_cas_odletu ASC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Vracia pole asociatívnych polí
    }
}
?>