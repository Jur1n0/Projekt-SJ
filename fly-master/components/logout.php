<?php
// logout.php
session_start(); // Spustí alebo obnoví session

// Zruší všetky session premenné
$_SESSION = array();

// Zničí session (vymaže súbor session zo servera)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Presmeruje používateľa na prihlasovaciu stránku
header("Location: login.php");
exit();
?>