<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Cart.php';
require_once '../classes/Flight.php'; // Potrebné pre získanie základnej ceny letu

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Pre aktualizáciu košíka sa musíte prihlásiť.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Neplatná požiadavka na aktualizáciu košíka.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items_data = $_POST['cart_items'] ?? [];

// Definovanie cien pre služby a odvoz (rovnaké ako v cart.php)
$pickup_dropoff_price_per_person = 50;
$service_prices = [
    'budget' => 0,
    'comfy' => 100,
    'luxury' => 200,
];

$success_count = 0;
$error_count = 0;

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);
    $flight_obj = new Flight($pdo_conn);

    foreach ($cart_items_data as $cart_item_id => $data) {
        $service_package = trim((string)($data['service_package'] ?? 'budget'));
        $pickup_service = isset($data['pickup_service']) ? 1 : 0;
        $dropoff_service = isset($data['dropoff_service']) ? 1 : 0;
        $notes = trim((string)($data['notes'] ?? ''));

        // Získame základnú cenu letu a kapacitu z formulára (hidden inputs)
        $base_price = filter_var($data['initial_price'], FILTER_VALIDATE_FLOAT);
        $capacity = filter_var($data['capacity'], FILTER_VALIDATE_INT);
        $new_price_from_js = filter_var($data['new_calculated_price'], FILTER_VALIDATE_FLOAT); // Cena vypočítaná na fronte

        if ($base_price === false || $capacity === false || $capacity <= 0 || $new_price_from_js === false) {
            $error_count++;
            error_log("Neplatné dáta pre položku košíka ID: {$cart_item_id}");
            continue;
        }

        // Prepočítame cenu na serveri pre validáciu a bezpečnosť
        $calculated_price = $base_price;
        $calculated_price += ($service_prices[$service_package] ?? 0) * $capacity;
        if ($pickup_service) {
            $calculated_price += $pickup_dropoff_price_per_person * $capacity;
        }
        if ($dropoff_service) {
            $calculated_price += $pickup_dropoff_price_per_person * $capacity;
        }

        // Môžete pridať porovnanie ceny vypočítanej na serveri s cenou poslanou z JS.
        // Ak sa líšia, môžeme logovať alebo zobraziť upozornenie.
        // Pre jednoduchosť použijeme cenu vypočítanú na serveri.
        // if (abs($calculated_price - $new_price_from_js) > 0.01) {
        //     error_log("Cena pre položku {$cart_item_id} sa líši: JS={$new_price_from_js}, Server={$calculated_price}");
        // }


        if ($cart_obj->updateCartItemServices($cart_item_id, $user_id, $service_package, $pickup_service, $dropoff_service, $calculated_price, $notes)) {
            $success_count++;
        } else {
            $error_count++;
        }
    }

    if ($success_count > 0 && $error_count === 0) {
        $_SESSION['message'] = "Košík bol úspešne aktualizovaný.";
        $_SESSION['message_type'] = "success";
    } elseif ($success_count > 0 && $error_count > 0) {
        $_SESSION['message'] = "Košík bol čiastočne aktualizovaný. Niektoré položky sa nepodarilo aktualizovať.";
        $_SESSION['message_type'] = "warning";
    } else {
        $_SESSION['message'] = "Chyba pri aktualizácii košíka. Žiadne položky neboli aktualizované.";
        $_SESSION['message_type'] = "error";
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri aktualizácii košíka: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Process update cart items error: " . $e->getMessage());
}

header("Location: ../components/cart.php");
exit();