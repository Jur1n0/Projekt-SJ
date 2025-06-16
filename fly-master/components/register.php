<?php
session_start();
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: user_dashboard.php");
    }
    exit();
}
?>
<?php include 'head.php'; ?>
<?php include 'header.php'; ?>

    <title>Registrácia - Fly</title>
    </head>
    <body>
<main id="registration-page" class="main-content">
    <div class="form-container">
        <h2>Registrácia</h2>
        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
            echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <form action="../process/process_register.php" method="POST">
            <div class="form-group">
                <label for="first_name">Meno:</label>
                <input type="text" id="first_name" name="first_name" required placeholder="Vaše krstné meno">
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
                <label for="confirm_password">Potvrdiť heslo:</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Zopakujte heslo">
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="gdpr_consent" name="gdpr_consent" value="1" required>
                <label for="gdpr_consent" class="gdpr-label">Súhlasím so spracovaním osobných údajov podľa GDPR.</label>
            </div>
            <button type="submit" class="btn-form-submit">Registrovať sa</button>
        </form>
        <p class="form-link-text">Už máte účet? <a href="login.php">Prihláste sa tu</a></p>
    </div>
</main>

<?php include 'footer.php'; ?>