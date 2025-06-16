<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../components/register.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

$first_name = trim((string)($_POST['first_name'] ?? ''));
$first_name = preg_replace('/[[:cntrl:]]/', '', $first_name);

$last_name = trim((string)($_POST['last_name'] ?? ''));
$last_name = preg_replace('/[[:cntrl:]]/', '', $last_name);

$email = trim((string)($_POST['email'] ?? ''));
$email = preg_replace('/[[:cntrl:]]/', '', $email);

$password = trim((string)($_POST['password'] ?? ''));
$password = preg_replace('/[[:cntrl:]]/', '', $password);

$confirm_password = trim((string)($_POST['confirm_password'] ?? ''));
$confirm_password = preg_replace('/[[:cntrl:]]/', '', $confirm_password);

$gdpr_consent = isset($_POST['gdpr_consent']) ? 1 : 0;

if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
    $_SESSION['message'] = "Prosím, vyplňte všetky povinné polia.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "Zadajte platnú e-mailovú adresu.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/register.php");
    exit();
}

if ($password !== $confirm_password) {
    $_SESSION['message'] = "Heslá sa nezhodujú.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/register.php");
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['message'] = "Heslo musí mať aspoň 6 znakov.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/register.php");
    exit();
}

if (!$gdpr_consent) {
    $_SESSION['message'] = "Musíte súhlasiť so spracovaním osobných údajov.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/register.php");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user = new User($pdo_conn);

    $user->first_name = $first_name;
    $user->last_name = $last_name;
    $user->email = $email;
    $user->password = $password;
    $user->gdpr_consent = $gdpr_consent;

    if ($user->register()) {
        $_SESSION['message'] = "Registrácia úspešná! Teraz sa môžete prihlásiť.";
        $_SESSION['message_type'] = "success";
        header("Location: ../components/login.php");
        exit();
    } else {
        $_SESSION['message'] = "Chyba pri registrácii. Skúste to prosím znova.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/register.php");
        exit();
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba: " . htmlspecialchars($e->getMessage());
    $_SESSION['message_type'] = "error";
    error_log("Registration error: " . $e->getMessage());
    header("Location: ../components/register.php");
    exit();
}
?>