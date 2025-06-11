<?php
session_start();

if (!isset($_SESSION['registration_success']) || $_SESSION['registration_success'] !== true) {
    header("Location: register.php");
    exit();
}

$registered_email = $_SESSION['registered_email'] ?? 'Váš účet';

unset($_SESSION['registration_success']);
unset($_SESSION['registered_email']);

?>
<?php include 'head.php'; ?>
<?php include 'header.php'; ?>

    <title>Registrácia úspešná - Fly</title>

    <main id="registration-success-page" class="main-content">
        <div class="form-container" style="padding: 40px; margin-top: 80px; margin-bottom: 80px;">
            <h2>Registrácia úspešná!</h2>
            <p>Ďakujeme, <?php echo htmlspecialchars($registered_email); ?>! Váš účet bol úspešne vytvorený.</p>
            <p>Teraz sa môžete prihlásiť a začať používať všetky naše služby.</p>
            <div class="btn-wrapper"> <a href="login.php" class="btn btn-primary">Prihlásiť sa</a>
                <a href="index.php" class="btn btn-secondary">Hlavná stránka</a>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>