<?php
session_start();
$message = $_SESSION['message'] ?? 'Ďakujeme za vašu objednávku!';
$message_type = $_SESSION['message_type'] ?? 'success';

unset($_SESSION['message']);
unset($_SESSION['message_type']);

?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<body id="top">

<?php include("header.php") ?>

<main>
    <article>
        <section class="section thankyou-page">
            <div class="container">
                <?php if ($message_type === 'success'): ?>
                    <ion-icon name="checkmark-circle-outline" class="icon"></ion-icon>
                    <h2>Objednávka úspešne dokončená!</h2>
                <?php else: ?>
                    <ion-icon name="information-circle-outline" class="icon" style="color: var(--blue);"></ion-icon>
                    <h2>Informácia o objednávke</h2>
                <?php endif; ?>

                <div class="message <?php echo htmlspecialchars($message_type); ?>">
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>

                <div class="btn-group">
                    <a href="index.php" class="btn btn-primary">Späť na úvod</a>
                    <a href="user_dashboard.php" class="btn btn-secondary">Zobraziť moje objednávky</a>
                </div>
            </div>
        </section>
    </article>
</main>

<?php include("footer.php") ?>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>