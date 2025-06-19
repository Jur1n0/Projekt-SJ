<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Sale.php';
require_once '../classes/Flight.php';
require_once '../classes/Cart.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Pre dokončenie objednávky sa musíte prihlásiť.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Neplatná požiadavka.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/cart.php");
    exit();
}

$payment_method = trim((string)($_POST['payment_method'] ?? ''));
$gdpr_consent_checkout = isset($_POST['gdpr_consent_checkout']) ? 1 : 0;

if (!$gdpr_consent_checkout) {
    $_SESSION['message'] = "Musíte súhlasiť so spracovaním osobných údajov a obchodnými podmienkami.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/checkout.php");
    exit();
}

if (empty($payment_method)) {
    $_SESSION['message'] = "Prosím, vyberte spôsob platby.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/checkout.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = true;
$error_message = '';

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);
    $sale_obj = new Sale($pdo_conn);

    // Získame položky z košíka na základe user_id
    $cart_items_stmt = $cart_obj->readByUserId($user_id);
    $cart_items = [];
    if ($cart_items_stmt->rowCount() > 0) {
        while ($row = $cart_items_stmt->fetch(PDO::FETCH_ASSOC)) {
            $cart_items[] = $row;
        }
    } else {
        $_SESSION['message'] = "Váš košík je prázdny. Nemôžete dokončiť objednávku.";
        $_SESSION['message_type'] = "warning";
        header("Location: ../components/cart.php");
        exit();
    }

    foreach ($cart_items as $item) {
        $flight_obj = new Flight($pdo_conn);
        $flight_obj->id = $item['flight_id'];
        if (!$flight_obj->readOne()) {
            $success = false;
            $error_message = "Chyba: Let s ID " . $item['flight_id'] . " nebol nájdený alebo nie je dostupný.";
            break;
        }

        $sale_obj->user_id = $user_id;
        $sale_obj->flight_id = $item['flight_id'];
        $sale_obj->total_price = $item['price_at_addition'];
        $sale_obj->service_package = $item['service_package'];
        $sale_obj->pickup_service = $item['pickup_service'];
        $sale_obj->dropoff_service = $item['dropoff_service'];
        $sale_obj->notes = $item['notes'];
        $sale_obj->payment_method = $payment_method;

        if (!$sale_obj->create()) {
            $success = false;
            $error_message = "Chyba pri vytváraní objednávky pre let ID: " . $item['flight_id'];
            error_log("Chyba checkoutu pre užívateľa " . $user_id . ": " . $error_message);
            break;
        }
    }

    if ($success) {
        if ($cart_obj->clearCart($user_id)) {
            $_SESSION['message'] = "Vaša objednávka bola úspešne dokončená! Potvrdenie bolo odoslané na váš e-mail.";
            $_SESSION['message_type'] = "success";
            header("Location: ../components/thankyou.php");
            exit();
        } else {
            $_SESSION['message'] = "Objednávka bola vytvorená, ale košík sa nepodarilo vyprázdniť.";
            $_SESSION['message_type'] = "warning";
            error_log("Order created for user " . $user_id . " but cart not cleared.");
            header("Location: ../components/thankyou.php");
            exit();
        }
    } else {
        $_SESSION['message'] = "Chyba pri dokončovaní objednávky: " . $error_message;
        $_SESSION['message_type'] = "error";
        header("Location: ../components/checkout.php");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Nastala neočakávaná chyba pri spracovaní objednávky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("General checkout error: " . $e->getMessage());
    header("Location: ../components/checkout.php");
    exit();
}
?>