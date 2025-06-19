<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/News.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

$idNews = filter_input(INPUT_POST, 'idNews', FILTER_VALIDATE_INT);

if (empty($idNews)) {
    $_SESSION['message'] = "ID novinky chýba.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php?section=news");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $news = new News($pdo_conn);

    $news->idNews = $idNews;

    if ($news->delete()) {
        $_SESSION['message'] = "Novinka bola úspešne vymazaná.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri mazaní novinky.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba databázy pri mazaní novinky: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Delete news error: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php?section=news");
exit();
?>