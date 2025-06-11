<?php
session_start();
// Zabezpečenie: Iba prihlásení používatelia (ktorí nie sú admini) majú prístup
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

$user_id = $_SESSION['user_id'];
$username = "Neznámy"; // Predvolené hodnoty
$email = "Neznámy";
$role = "Neznáma";
$first_name = "Neznámy";
$last_name = "";

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user_obj = new User($pdo_conn);
    $user_obj->id = $user_id;

    if ($user_obj->readOne()) {
        $first_name = $user_obj->first_name;
        $last_name = $user_obj->last_name;
        $email = $user_obj->email;
        $role = $user_obj->role;
    } else {
        // Používateľ sa nenašiel v DB, aj keď bol v session. Chyba.
        $_SESSION['message'] = "Chyba: Vaše dáta sa nenašli.";
        $_SESSION['message_type'] = "error";
        // Odhlásiť používateľa pre bezpečnosť
        header("Location: logout.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní profilu: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("User dashboard data fetch error: " . $e->getMessage());
}
?>
<?php include("head.php") ?>
<?php include 'header.php'; ?>

    <title>Môj účet - Fly</title>

    <main id="user-dashboard-page" class="main-content">
        <div class="dashboard-container">
            <h2>Vitajte, <?php echo htmlspecialchars($first_name); ?>!</h2>
            <?php
            if (isset($_SESSION['message'])) {
                $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
                echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            <p>Toto je váš osobný účet. Tu môžete spravovať svoje rezervácie a osobné údaje.</p>

            <h3>Vaše údaje:</h3>
            <p><strong>Meno:</strong> <?php echo htmlspecialchars($first_name); ?></p>
            <p><strong>Priezvisko:</strong> <?php echo htmlspecialchars($last_name); ?></p>
            <p><strong>E-mail:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Rola:</strong> <?php echo htmlspecialchars($role); ?></p>

            <p>Pridajte tu ďalší obsah pre užívateľský dashboard.</p>

            <a href="logout.php" class="btn-logout">Odhlásiť sa</a>
        </div>
    </main>

<?php include 'footer.php'; ?>