<?php
// process/process_update_user.php
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

$user_id = $_POST['user_id'] ?? '';
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? ''); // Získame rolu z formulára

// Základná validácia
if (empty($user_id) || empty($first_name) || empty($last_name) || empty($email) || empty($role)) {
    $_SESSION['message'] = "Všetky polia sú povinné.";
    $_SESSION['message_type'] = "error";
    header("Location: ../components/admin_dashboard.php");
    exit();
}

// Voliteľná validácia roly (odporúčané)
$allowed_roles = ['user', 'admin']; // Definujte platné roly
if (!in_array(strtolower($role), $allowed_roles)) { // Použite strtolower pre porovnanie
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
    $user->role = strtolower($role); // Uložte rolu malými písmenami do DB

    $success_message = "Používateľ bol úspešne aktualizovaný.";
    $error_message = "Chyba pri aktualizácii používateľa. Možno e-mail už existuje.";

    // Pokus o aktualizáciu základných dát (meno, priezvisko, email)
    // Funkcia update() bola upravená tak, aby spracovala email unikátnosť
    if ($user->update()) {
        // Po úspešnej aktualizácii mena, priezviska a emailu, aktualizujeme aj rolu
        // Použijeme metódu updateRole() z User.php
        if ($user->updateRole()) {
            $_SESSION['message'] = $success_message . " Rola bola tiež aktualizovaná.";
            $_SESSION['message_type'] = "success";
        } else {
            // Ak základné dáta prešli, ale rola zlyhala
            $_SESSION['message'] = $success_message . " Avšak, rolu sa nepodarilo zmeniť.";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        // Ak zlyhala aktualizácia základných dát (napr. duplicitný email)
        $_SESSION['message'] = $error_message;
        $_SESSION['message_type'] = "error";
    }

} catch (Exception $e) {
    // Všeobecná chyba, napr. problémy s databázou
    $_SESSION['message'] = "Nastala chyba: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Update user error: " . $e->getMessage()); // Zalogovať pre debugging
}

header("Location: ../components/admin_dashboard.php");
exit();
?>