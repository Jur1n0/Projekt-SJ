<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = "Nemáte oprávnenie na vykonanie tejto operácie.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

$user_id = $_POST['user_id'] ?? '';
$first_name = trim((string)($_POST['first_name'] ?? ''));
$first_name = preg_replace('/[[:cntrl:]]/', '', $first_name);

$last_name = trim((string)($_POST['last_name'] ?? ''));
$last_name = preg_replace('/[[:cntrl:]]/', '', $last_name);

$email = trim((string)($_POST['email'] ?? ''));
$email = preg_replace('/[[:cntrl:]]/', '', $email);

$role = trim((string)($_POST['role'] ?? ''));
$role = preg_replace('/[[:cntrl:]]/', '', $role);


if (empty($user_id) || empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
    $_SESSION['message'] = "Všetky polia sú povinné.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

$allowed_roles = ['user', 'admin'];
if (!in_array(strtolower($role), $allowed_roles)) {
    $_SESSION['message'] = "Neplatná rola. Povolené roly sú: " . implode(', ', $allowed_roles) . ".";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user = new User($pdo_conn);

    $user->id = $user_id;
    $user->first_name = $first_name;
    $user->last_name = $last_name;
    $user->email = $email;
    $user->role = strtolower($role);

    $success_message = "Používateľ bol úspešne aktualizovaný.";
    $error_message = "Chyba pri aktualizácii používateľa. Možno e-mail už existuje alebo používateľské meno.";

    if ($user->update()) {
        if ($user->updateRole()) {
            $_SESSION['message'] = $success_message . " Rola bola tiež aktualizovaná.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = $success_message . " Avšak, rolu sa nepodarilo zmeniť.";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = $error_message;
        $_SESSION['message_type'] = "error";
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Nastala chyba: " . htmlspecialchars($e->getMessage());
    $_SESSION['message_type'] = "error";
    error_log("Update user error: " . $e->getMessage());
}

header("Location: ../components/admin_dashboard.php");
exit();
?>