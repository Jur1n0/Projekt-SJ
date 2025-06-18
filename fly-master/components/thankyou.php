<?php
session_start();
// Získanie správy o úspechu
$message = $_SESSION['message'] ?? 'Ďakujeme za vašu objednávku!';
$message_type = $_SESSION['message_type'] ?? 'success';

// Vyčistíme session správy
unset($_SESSION['message']);
unset($_SESSION['message_type']);

?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<style>
    .thankyou-page {
        text-align: center;
        padding: 50px 20px;
        background-color: var(--background-primary);
        color: var(--text-color);
        min-height: calc(100vh - 200px); /* Upravte podľa výšky hlavičky a pätičky */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .thankyou-page .icon {
        font-size: 80px;
        color: var(--green); /* Farba pre úspešnú ikonu */
        margin-bottom: 20px;
    }

    .thankyou-page h2 {
        font-size: 2.5rem;
        color: var(--heading-color);
        margin-bottom: 20px;
    }

    .thankyou-page p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 30px;
        max-width: 600px;
    }

    .thankyou-page .btn {
        padding: 12px 25px;
        font-size: 1.1rem;
        margin: 0 10px;
    }

    .message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
        font-weight: bold;
        max-width: 500px;
        width: 100%;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .info-message {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .warning-message {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
</style>
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