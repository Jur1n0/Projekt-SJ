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

$lietadlo = trim((string)($_POST['lietadlo'] ?? ''));
$miesto_odletu = trim((string)($_POST['miesto_odletu'] ?? ''));
$miesto_priletu = trim((string)($_POST['miesto_priletu'] ?? ''));
$datum_cas_odletu_str = trim((string)($_POST['datum_cas_odletu'] ?? ''));
$datum_cas_priletu_str = trim((string)($_POST['datum_cas_priletu'] ?? ''));
$cena = filter_input(INPUT_POST, 'cena', FILTER_VALIDATE_FLOAT);
$kapacita_lietadla = filter_input(INPUT_POST, 'kapacita_lietadla', FILTER_VALIDATE_INT);
$dlzka_letu_hodiny = filter_input(INPUT_POST, 'dlzka_letu_hodiny', FILTER_VALIDATE_INT);
$dlzka_letu_minuty = filter_input(INPUT_POST, 'dlzka_letu_minuty', FILTER_VALIDATE_INT);
$obrazok = trim((string)($_POST['obrazok'] ?? ''));

if (empty($lietadlo) || empty($miesto_odletu) || empty($miesto_priletu) ||
    empty($datum_cas_odletu_str) || empty($datum_cas_priletu_str) ||
    $cena === false || $cena <= 0 ||
    $kapacita_lietadla === false || $kapacita_lietadla <= 0 ||
    $dlzka_letu_hodiny === false || $dlzka_letu_hodiny < 0 ||
    $dlzka_letu_minuty === false || $dlzka_letu_minuty < 0 || $dlzka_letu_minuty >= 60)
{
    $_SESSION['message'] = "Všetky povinné polia musia byť vyplnené a mať platný formát.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=flights");
    exit();
}

try {
    $datum_cas_odletu = new DateTime($datum_cas_odletu_str);
    $datum_cas_priletu = new DateTime($datum_cas_priletu_str);
} catch (Exception $e) {
    $_SESSION['message'] = "Neplatný formát dátumu a času. Použite napr. 'YYYY-MM-DD HH:MM'.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=flights");
    exit();
}

if ($datum_cas_priletu <= $datum_cas_odletu) {
    $_SESSION['message'] = "Dátum a čas príletu musí byť po dátume a čase odletu.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=flights");
    exit();
}


try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $flight = new Flight($pdo_conn);

    $flight->lietadlo = $lietadlo;
    $flight->miesto_odletu = $miesto_odletu;
    $flight->miesto_priletu = $miesto_priletu;
    $flight->datum_cas_odletu = $datum_cas_odletu->format('Y-m-d H:i:s');
    $flight->datum_cas_priletu = $datum_cas_priletu->format('Y-m-d H:i:s');
    $flight->cena = $cena;
    $flight->kapacita_lietadla = $kapacita_lietadla;
    $flight->dlzka_letu_hodiny = $dlzka_letu_hodiny;
    $flight->dlzka_letu_minuty = $dlzka_letu_minuty;
    $flight->obrazok = !empty($obrazok) ? $obrazok : null;

    if ($flight->create()) {
        $_SESSION['message'] = "Let bol úspešne pridaný.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri pridávaní letu.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba databázy: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Error adding flight: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php?section=flights");
exit();
?>