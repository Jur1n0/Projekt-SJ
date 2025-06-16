<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/News.php';

// Iba admin môže upravovať novinky
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

$idNews = filter_input(INPUT_POST, 'idNews', FILTER_VALIDATE_INT);
$nadpis = trim((string)($_POST['Nadpis'] ?? ''));
$text = trim((string)($_POST['Text'] ?? ''));
$obrazok = trim((string)($_POST['Obrazok'] ?? ''));

if (empty($idNews) || empty($nadpis) || empty($text)) {
    $_SESSION['message'] = "ID, nadpis a text novinky sú povinné.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=news"); // Presmerovanie späť na sekciu noviniek
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $news = new News($pdo_conn);

    $news->idNews = $idNews;
    $news->Nadpis = $nadpis;
    $news->Text = $text;
    $news->Obrazok = !empty($obrazok) ? $obrazok : null;

    if ($news->update()) {
        $_SESSION['message'] = "Novinka bola úspešne aktualizovaná.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri aktualizácii novinky.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba databázy pri aktualizácii novinky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Update news error: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php?section=news"); // Presmerovanie späť na sekciu noviniek
exit();
?>