<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Flight.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id === false) {
    $_SESSION['message'] = "ID letu chýba alebo je neplatné.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=flights");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $flight = new Flight($pdo_conn);

    $flight->id = $id;

    if ($flight->delete()) {
        $_SESSION['message'] = "Let bol úspešne vymazaný.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri mazaní letu.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba databázy: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Error deleting flight: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php?section=flights");
exit();
?>