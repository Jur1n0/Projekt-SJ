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

    <title>Prihlásenie - Fly</title>
    </head>
    <body>
<main id="login-page" class="main-content">
    <div class="form-container">
        <h2>Prihlásenie</h2>
        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
            echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <form action="../process/process_login.php" method="POST">
            <div class="form-group">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required placeholder="Váš e-mail">
            </div>
            <div class="form-group">
                <label for="password">Heslo:</label>
                <input type="password" id="password" name="password" required placeholder="Vaše heslo">
            </div>
            <button type="submit" class="btn-form-submit">Prihlásiť sa</button>
        </form>
        <p class="form-link-text">Nemáte účet? <a href="register.php">Registrujte sa tu</a></p>
    </div>
</main>

<?php include 'footer.php'; ?>