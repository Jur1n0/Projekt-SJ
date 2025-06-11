<?php
// process/process_delete_user.php
session_start();

// Zabezpečenie: Iba prihlásení admini môžu pristupovať k tomuto skriptu
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

$user_id = $_POST['user_id'] ?? null;

if (empty($user_id)) {
    $_SESSION['message'] = "ID používateľa chýba.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

// Dôležité zabezpečenie: Admin nemôže vymazať sám seba!
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['message'] = "Nemôžete vymazať svoj vlastný admin účet.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user = new User($pdo_conn);

    $user->id = $user_id;

    if ($user->delete()) {
        $_SESSION['message'] = "Používateľ bol úspešne vymazaný.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Chyba pri vymazávaní používateľa.";
        $_SESSION['message_type'] = "error";
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Nastala chyba: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Delete user error: " . $e->getMessage()); // Zalogovať pre debugging
}

header("Location: ../components/admin_dashboard.php");
exit();
?>