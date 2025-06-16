<?php
?>
<header class="header" data-header>
    <link rel="stylesheet" href="../assets/css/style.css">
    <div class="container">
        <a href="index.php" class="logo">
            <img src="../assets/images/logo.svg" width="102" height="48" alt="fly logo">
        </a>
        <nav class="navbar" data-navbar>
            <div class="navbar-top">
                <a href="index.php" class="logo">
                    <img src="../assets/images/logo.svg" width="102" height="48" alt="fly logo">
                </a>
                <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
                    <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
                </button>
            </div>
            <ul class="navbar-list">
                <li class="navbar-item">
                    <a href="index.php#home" class="navbar-link" data-nav-link>Home</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#service" class="navbar-link" data-nav-link>Services</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#about" class="navbar-link" data-nav-link>About us</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#news" class="navbar-link" data-nav-link>News</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#offer" class="navbar-link" data-nav-link>Shop</a>
                </li>
                <li class="navbar-item">
                    <a href="index.php#contact" class="navbar-link" data-nav-link>Contact</a>
                </li>
            </ul>
        </nav>
        <div class="header-action">
            <?php
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                ?>
                <a href="user_dashboard.php" class="header-action-btn" aria-label="person">
                    <ion-icon name="person" aria-hidden="true"></ion-icon>
                </a>
                <?php
            } else {
                ?>
                <a href="login.php" class="header-action-btn" aria-label="person">
                    <ion-icon name="person-outline" aria-hidden="true"></ion-icon>
                </a>
                <?php
            }
            ?>
            <button class="header-action-btn" aria-label="cart">
                <ion-icon name="cart-outline" aria-hidden="true"></ion-icon>
            </button
            <button class="header-action-btn nav-open-btn" aria-label="menu" data-nav-toggler>
                <ion-icon name="menu-outline" aria-hidden="true"></ion-icon>
            </button>
        </div>
        <div class="overlay" data-nav-toggler data-overlay></div>
    </div>
</header>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>