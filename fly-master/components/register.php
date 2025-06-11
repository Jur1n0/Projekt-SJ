<?php
session_start();
// Zabezpečenie presmerovania, ak je používateľ už prihlásený
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>
<?php include 'head.php';?>
<?php include 'header.php'; // Includuje header z rovnakého adresára, ktorý by mal obsahovať aj head tagy ?>

    <title>Registrácia - Fly</title>
    <main id="register-page" class="main-content">
        <div class="form-container">
            <h2>Registrácia</h2>
            <?php
            // Zobraz správy (napr. chybné vyplnenie formulára, už existujúci e-mail)
            if (isset($_SESSION['message'])) {
                $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
                echo "<p class='message {$class}'>" . $_SESSION['message'] . "</p>";
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
            <form action="../process/process_register.php" method="POST">
                <div class="form-group">
                    <label for="first_name">Meno:</label>
                    <input type="text" id="first_name" name="first_name" required placeholder="Vaše meno">
                </div>
                <div class="form-group">
                    <label for="last_name">Priezvisko:</label>
                    <input type="text" id="last_name" name="last_name" required placeholder="Vaše priezvisko">
                </div>
                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required placeholder="Váš e-mail">
                </div>
                <div class="form-group">
                    <label for="password">Heslo:</label>
                    <input type="password" id="password" name="password" required placeholder="Vaše heslo">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Potvrdiť heslo:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required placeholder="Potvrďte heslo">
                </div>
                <div class="form-group" style="text-align: center;">
                    <input type="checkbox" id="gdpr" name="gdpr" required>
                    <label for="gdpr">Súhlasím so <a href="privacy.php" target="_blank">spracovaním osobných údajov</a> (GDPR)</label>
                </div>
                <button type="submit" class="btn-form-submit">Registrovať sa</button>
            </form>
            <p class="form-link-text">Už máte účet? <a href="login.php">Prihláste sa</a></p>
        </div>
    </main>

<?php include 'footer.php'; ?>