<?php
session_start();
require_once __DIR__ . '/../module/Database.php'; // Uprav cestu, ak je iná

// Skontroluj, či bol formulár odoslaný metódou POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Získaj údaje z formulára
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);// Predpokladám, že 'username' bude použité ako first_name
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $gdpr_consent = isset($_POST['gdpr']) ? 1 : 0; // 1 ak je zaškrtnuté, 0 ak nie

    // Inicializácia premenných pre chyby
    $errors = [];

    // Validácia dát
    if (empty($first_name)) {
        $errors[] = "Meno je povinné.";
    }
    if (empty($last_name)) {
        $errors[] = "Priezvisko je povinné.";
    }
    if (empty($email)) {
        $errors[] = "E-mail je povinný.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Zadajte platný e-mail.";
    }
    if (empty($password)) {
        $errors[] = "Heslo je povinné.";
    } elseif (strlen($password) < 6) { // Minimálna dĺžka hesla
        $errors[] = "Heslo musí mať aspoň 6 znakov.";
    }
    if ($password !== $password_confirm) {
        $errors[] = "Heslá sa nezhodujú.";
    }
    if ($gdpr_consent == 0) {
        $errors[] = "Musíte súhlasiť so spracovaním osobných údajov.";
    }

    // Ak sú chyby, presmeruj späť na registračnú stránku s chybovými správami
    if (!empty($errors)) {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = 'error';
        header("Location: ../components/register.php");
        exit();
    }

    // Pokus o pripojenie k databáze
    try {
        $database = new Database();
        $conn = $database->getConnection();

        // Skontroluj, či e-mail už existuje v databáze
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $_SESSION['message'] = "Používateľ s týmto e-mailom už existuje.";
            $_SESSION['message_type'] = 'error';
            header("Location: ../components/register.php");
            exit();
        }

        // Hashovanie hesla (dôležité pre bezpečnosť!)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Priprav vloženie dát do tabuľky 'users'
        // 'last_name' a 'role' sú pevne dané alebo ich musíš pridať do formulára
        // V tomto príklade nastavím 'last_name' na prázdny reťazec a 'role' na 'user'.
        // Ak chceš 'last_name' z formulára, musíš pridať input pole do register.php
        $last_name = ''; // Zatiaľ prázdne, ak nemáš input pre last_name
        $role = 'user'; // Predvolená rola pre nového používateľa

        $stmt = $conn->prepare(
            "INSERT INTO users (first_name, last_name, email, password, gdpr_consent, role)
             VALUES (:first_name, :last_name, :email, :password, :gdpr_consent, :role)"
        );

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':gdpr_consent', $gdpr_consent, PDO::PARAM_INT);
        $stmt->bindParam(':role', $role);

        // Vykonanie príkazu
        if ($stmt->execute()) {
            // Registrácia úspešná, presmeruj na potvrdzujúcu stránku
            $_SESSION['registration_success'] = true;
            $_SESSION['registered_email'] = $email; // Ulož e-mail pre zobrazenie na potvrdzujúcej stránke
            header("Location: ../components/registration_success.php");
            exit();
        } else {
            // Chyba pri vkladaní do DB
            $_SESSION['message'] = "Nastala chyba pri registrácii. Skúste to znova neskôr.";
            $_SESSION['message_type'] = 'error';
            header("Location: ../components/register.php");
            exit();
        }

    } catch (PDOException $e) {
        // Chyba pripojenia alebo vykonávania databázového príkazu
        $_SESSION['message'] = "Chyba databázy: " . $e->getMessage();
        $_SESSION['message_type'] = 'error';
        header("Location: ../components/register.php");
        exit();
    }

} else {
    // Ak nebol formulár odoslaný metódou POST, presmeruj na registračnú stránku
    header("Location: ../components/register.php");
    exit();
}
?>