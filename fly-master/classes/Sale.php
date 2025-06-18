<?php
class Sale {
    private $conn;
    private $table_name = "sales";
    private $flights_table_name = "flights"; // Pre získanie detailov letu
    private $users_table_name = "users";     // Pre získanie detailov používateľa

    public $id_sale;
    public $user_id;
    public $flight_id;
    public $total_price;
    public $service_package;
    public $pickup_service;
    public $dropoff_service;
    public $notes;
    public $payment_method;
    public $payment_status; // 'pending', 'paid'
    public $order_status;   // 'pending', 'cancelled', 'completed'
    public $sale_date;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Metóda na vytvorenie novej objednávky (po checkoute)
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      user_id = :user_id,
                      flight_id = :flight_id,
                      total_price = :total_price,
                      service_package = :service_package,
                      pickup_service = :pickup_service,
                      dropoff_service = :dropoff_service,
                      notes = :notes,
                      payment_method = :payment_method,
                      payment_status = 'pending',
                      order_status = 'pending',
                      sale_date = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->flight_id = htmlspecialchars(strip_tags($this->flight_id));
        $this->total_price = htmlspecialchars(strip_tags($this->total_price));
        $this->service_package = htmlspecialchars(strip_tags($this->service_package));
        $this->pickup_service = htmlspecialchars(strip_tags($this->pickup_service));
        $this->dropoff_service = htmlspecialchars(strip_tags($this->dropoff_service));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':flight_id', $this->flight_id);
        $stmt->bindParam(':total_price', $this->total_price);
        $stmt->bindParam(':service_package', $this->service_package);
        $stmt->bindParam(':pickup_service', $this->pickup_service, PDO::PARAM_INT);
        $stmt->bindParam(':dropoff_service', $this->dropoff_service, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':payment_method', $this->payment_method);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri vytváraní objednávky: " . $e->getMessage());
            return false;
        }
    }

    // Metóda na čítanie všetkých objednávok (pre admina)
    public function readAll() {
        $query = "SELECT s.*, f.lietadlo, f.miesto_odletu, f.miesto_priletu, f.datum_cas_odletu,
                         u.first_name, u.last_name, u.email
                  FROM " . $this->table_name . " s
                  JOIN " . $this->flights_table_name . " f ON s.flight_id = f.id
                  JOIN " . $this->users_table_name . " u ON s.user_id = u.id
                  ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Metóda na čítanie objednávok pre konkrétneho používateľa
    public function readByUserId($user_id) {
        $query = "SELECT s.*, f.lietadlo, f.miesto_odletu, f.miesto_priletu, f.datum_cas_odletu
                  FROM " . $this->table_name . " s
                  JOIN " . $this->flights_table_name . " f ON s.flight_id = f.id
                  WHERE s.user_id = :user_id
                  ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Metóda na čítanie jednej objednávky (pre validáciu statusu)
    public function readOne() {
        $query = "SELECT s.payment_status, s.order_status
                  FROM " . $this->table_name . " s
                  WHERE s.id_sale = :id_sale
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_sale', $this->id_sale, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->payment_status = $row['payment_status'];
            $this->order_status = $row['order_status'];
            return true;
        }
        return false;
    }

    // Metóda na aktualizáciu stavu objednávky a platby
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      payment_status = :payment_status,
                      order_status = :order_status,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id_sale = :id_sale";

        $stmt = $this->conn->prepare($query);

        $this->payment_status = htmlspecialchars(strip_tags($this->payment_status));
        $this->order_status = htmlspecialchars(strip_tags($this->order_status));
        $this->id_sale = htmlspecialchars(strip_tags($this->id_sale));

        $stmt->bindParam(':payment_status', $this->payment_status);
        $stmt->bindParam(':order_status', $this->order_status);
        $stmt->bindParam(':id_sale', $this->id_sale, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii statusu objednávky: " . $e->getMessage());
            return false;
        }
    }
}
?>