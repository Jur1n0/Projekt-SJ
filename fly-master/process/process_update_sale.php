<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Sale.php';

// Iba admin môže aktualizovať objednávky
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

$id_sale = filter_input(INPUT_POST, 'id_sale', FILTER_VALIDATE_INT);
$payment_status = trim((string)($_POST['payment_status'] ?? ''));
$order_status = trim((string)($_POST['order_status'] ?? ''));

if (empty($id_sale) || empty($payment_status) || empty($order_status)) {
    $_SESSION['message'] = "Všetky polia (ID objednávky, stav platby, stav objednávky) sú povinné.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=orders");
    exit();
}

// Overenie platných hodnôt pre ENUM
$allowed_payment_statuses = ['pending', 'paid'];
$allowed_order_statuses = ['pending', 'cancelled', 'completed'];

if (!in_array($payment_status, $allowed_payment_statuses)) {
    $_SESSION['message'] = "Neplatný stav platby.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=orders");
    exit();
}

if (!in_array($order_status, $allowed_order_statuses)) {
    $_SESSION['message'] = "Neplatný stav objednávky.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=orders");
    exit();
}

// Validácia: Objednávka môže byť 'completed' iba ak je 'paid'
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $sale_check = new Sale($pdo_conn);
    $sale_check->id_sale = $id_sale;

    if (!$sale_check->readOne()) {
        $_SESSION['message'] = "Objednávka s daným ID sa nenašla.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/admin_dashboard.php?section=orders");
        exit();
    }

    // Ak sa snažíme nastaviť 'completed', ale platba nie je 'paid'
    if ($order_status === 'completed' && $payment_status !== 'paid') {
        $_SESSION['message'] = "Objednávka nemôže byť dokončená, kým nie je zaplatená.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/admin_dashboard.php?section=orders");
        exit();
    }

    // Ak sa snažíme zmeniť stav platby na 'pending', ale objednávka je už 'completed'
    if ($payment_status === 'pending' && $sale_check->order_status === 'completed') {
        $_SESSION['message'] = "Objednávka nemôže byť označená ako 'čakajúca na platbu', ak je už 'dokončená'.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/admin_dashboard.php?section=orders");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri validácii aktuálneho stavu objednávky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin update sale status validation error: " . $e->getMessage());
    header("Location: ../components/admin_dashboard.php?section=orders");
    exit();
}


try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $sale = new Sale($pdo_conn);

    $sale->id_sale = $id_sale;
    $sale->payment_status = $payment_status;
    $sale->order_status = $order_status;


    if ($sale->updateStatus()) {
        $_SESSION['message'] = "Stav objednávky bol úspešne aktualizovaný.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri aktualizácii stavu objednávky.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Nastala neočakávaná chyba pri aktualizácii stavu objednávky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Process update sale error: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php?section=orders");
exit();
?>