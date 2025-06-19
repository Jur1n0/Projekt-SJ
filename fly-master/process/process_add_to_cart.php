<?php
session_start();

require_once '../module/Database.php';
require_once '../classes/Flight.php';
require_once '../classes/Cart.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Neplatná požiadavka.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/index.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Pre pridanie letu do košíka sa musíte prihlásiť.";
    $_SESSION['message_type'] = "info";
    header("Location: ../components/login.php");
    exit();
}

$flight_id = filter_input(INPUT_POST, 'flight_id', FILTER_VALIDATE_INT);
$base_price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

if ($flight_id === false || $flight_id <= 0 || $base_price === false) {
    $_SESSION['message'] = "Neplatné ID letu alebo cena.";
    $_SESSION['message_type'] = "error";
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../components/flights.php'));
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);

    $cart_obj->user_id = $_SESSION['user_id'];
    $cart_obj->flight_id = $flight_id;
    $cart_obj->price_at_addition = $base_price;
    $cart_obj->service_package = 'budget';
    $cart_obj->pickup_service = 0;
    $cart_obj->dropoff_service = 0;
    $cart_obj->notes = '';

    if ($cart_obj->addToCart()) {
        $_SESSION['message'] = "Let bol úspešne pridaný do košíka! Doplnkové služby si môžete vybrať priamo v košíku.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Let už je vo vašom košíku, alebo nastala chyba pri pridávaní. Ak si želáte zmeniť služby, prejdite do košíka.";
        $_SESSION['message_type'] = "warning";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri spracovaní požiadavky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Add to cart error: " . $e->getMessage());
}

header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../components/flights.php'));
exit();