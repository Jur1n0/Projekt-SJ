<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../components/login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

// Získanie a OČISTENIE dát
$email = trim((string)($_POST['email'] ?? ''));
$email = preg_replace('/[[:cntrl:]]/', '', $email); // Odstráni riadiace znaky

$password = trim((string)($_POST['password'] ?? ''));
$password = preg_replace('/[[:cntrl:]]/', '', $password); // Odstráni riadiace znaky

if (empty($email) || empty($password)) {
    $_SESSION['message'] = "Prosím, vyplňte obe polia (e-mail a heslo).";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/login.php");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user = new User($pdo_conn);

    $user->email = $email;
    $user->password = $password;

    if ($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $user->role;
        $_SESSION['username'] = $user->username;

        $_SESSION['message'] = "Prihlásenie úspešné!";
        $_SESSION['message_type'] = "success";

        if ($user->role === 'admin') {
            header("Location: ../components/admin_dashboard.php");
        } else {
            header("Location: ../components/user_dashboard.php");
        }
        exit();
    } else {
        $_SESSION['message'] = "Nesprávny e-mail alebo heslo.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/login.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Nastala chyba pri prihlásení. Skúste to prosím neskôr.";
    $_SESSION['message_type'] = "error";
    error_log("Login error: " . $e->getMessage()); // Zostáva len error_log
    header("Location: ../components/login.php");
    exit();
}
?>