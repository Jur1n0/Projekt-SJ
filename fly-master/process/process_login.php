<?php
// process/process_login.php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../components/login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

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
        // Používateľ nájdený a heslo overené v tabuľke 'users'
        // Session premenné sú už nastavené v $user objekte po úspešnom prihlásení v User.php
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $user->role; // Získa rolu z databázy (admin/user)
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;

        $_SESSION['message'] = "Prihlásenie úspešné!";
        $_SESSION['message_type'] = "success";

        if ($user->role === 'admin') {
            header("Location: ../components/admin_dashboard.php");
        } else {
            header("Location: ../components/user_dashboard.php");
        }
        exit();
    } else {
        // Prihlásenie zlyhalo
        $_SESSION['message'] = "Nesprávny e-mail alebo heslo.";
        $_SESSION['message_type'] = "error";
        header("Location: ../components/login.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Nastala chyba pri prihlásení: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Login error: " . $e->getMessage());
    header("Location: ../components/login.php");
    exit();
}
?>