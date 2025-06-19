<?php
class Cart
{
    private $conn;
    private $table_name = "cart_items";
    private $flights_table_name = "flights";

    public $id;
    public $user_id;
    public $flight_id;
    public $price_at_addition;
    public $service_package;
    public $pickup_service;
    public $dropoff_service;
    public $notes;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function addToCart()
    {
        $query_check = "SELECT id FROM " . $this->table_name . "
                        WHERE user_id = :user_id AND flight_id = :flight_id LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':user_id', $this->user_id);
        $stmt_check->bindParam(':flight_id', $this->flight_id);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  SET
                      user_id = :user_id,
                      flight_id = :flight_id,
                      price_at_addition = :price_at_addition,
                      service_package = :service_package,
                      pickup_service = :pickup_service,
                      dropoff_service = :dropoff_service,
                      notes = :notes,
                      added_at = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->flight_id = htmlspecialchars(strip_tags($this->flight_id));
        $this->price_at_addition = htmlspecialchars(strip_tags($this->price_at_addition));
        $this->service_package = htmlspecialchars(strip_tags($this->service_package));
        $this->pickup_service = htmlspecialchars(strip_tags($this->pickup_service));
        $this->dropoff_service = htmlspecialchars(strip_tags($this->dropoff_service));
        $this->notes = htmlspecialchars(strip_tags($this->notes));

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':flight_id', $this->flight_id);
        $stmt->bindParam(':price_at_addition', $this->price_at_addition);
        $stmt->bindParam(':service_package', $this->service_package);
        $stmt->bindParam(':pickup_service', $this->pickup_service, PDO::PARAM_INT);
        $stmt->bindParam(':dropoff_service', $this->dropoff_service, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $this->notes);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri pridávaní do košíka: " . $e->getMessage());
            return false;
        }
    }

    public function readByUserId($user_id)
    {
        $query = "SELECT ci.*, f.cena as flight_base_price, f.kapacita_lietadla
                  FROM " . $this->table_name . " ci
                  JOIN " . $this->flights_table_name . " f ON ci.flight_id = f.id
                  WHERE ci.user_id = :user_id
                  ORDER BY ci.added_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function updateCartItemServices(
        $cart_item_id, $user_id, $service_package, $pickup_service, $dropoff_service, $new_calculated_price, $notes
    ) {
        $query = "UPDATE " . $this->table_name . "
                  SET
                      service_package = :service_package,
                      pickup_service = :pickup_service,
                      dropoff_service = :dropoff_service,
                      notes = :notes,
                      price_at_addition = :new_calculated_price,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE id = :cart_item_id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $service_package = htmlspecialchars(strip_tags($service_package));
        $pickup_service = htmlspecialchars(strip_tags($pickup_service));
        $dropoff_service = htmlspecialchars(strip_tags($dropoff_service));
        $notes = htmlspecialchars(strip_tags($notes));
        $new_calculated_price = htmlspecialchars(strip_tags($new_calculated_price));

        $stmt->bindParam(':cart_item_id', $cart_item_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':service_package', $service_package);
        $stmt->bindParam(':pickup_service', $pickup_service, PDO::PARAM_INT);
        $stmt->bindParam(':dropoff_service', $dropoff_service, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':new_calculated_price', $new_calculated_price);

        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri aktualizácii služieb košíka: " . $e->getMessage());
            return false;
        }
    }


    public function deleteCartItem()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri mazaní položky košíka: " . $e->getMessage());
            return false;
        }
    }

    public function clearCart($user_id)
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Chyba pri vyprázdňovaní košíka: " . $e->getMessage());
            return false;
        }
    }
}
?>