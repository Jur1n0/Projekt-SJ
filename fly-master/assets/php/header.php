
<header class="header" data-header>
    <div class="container">

        <a href="#" class="logo">
            <img src="./assets/images/logo.svg" width="102" height="48" alt="fly logo">
        </a>

        <nav class="navbar" data-navbar>

            <div class="navbar-top">
                <a href="#" class="logo">
                    <img src="./assets/images/logo.svg" width="102" height="48" alt="fly logo">
                </a>

                <button class="nav-close-btn" aria-label="close menu" data-nav-toggler>
                    <ion-icon name="close-outline" aria-hidden="true"></ion-icon>
                </button>
            </div>

            <ul class="navbar-list">

                <li class="navbar-item">
                    <a href="#home" class="navbar-link" data-nav-link>Home</a>
                </li>

                <li class="navbar-item">
                    <a href="#services" class="navbar-link" data-nav-link>Services</a>
                </li>

                <li class="navbar-item">
                    <a href="#" class="navbar-link" data-nav-link>News</a>
                </li>

                <li class="navbar-item">
                    <a href="#" class="navbar-link" data-nav-link>Shop</a>
                </li>

                <li class="navbar-item">
                    <a href="#" class="navbar-link" data-nav-link>Contact</a>
                </li>

            </ul>

        </nav>

        <div class="header-action">

            <button class="header-action-btn" aria-label="search">
                <ion-icon name="search-outline" aria-hidden="true"></ion-icon>
            </button>

            <button class="header-action-btn" aria-label="cart">
                <ion-icon name="cart-outline" aria-hidden="true"></ion-icon>
            </button>

            <button class="header-action-btn nav-open-btn" aria-label="menu" data-nav-toggler>
                <ion-icon name="menu-outline" aria-hidden="true"></ion-icon>
            </button>

        </div>

        <div class="overlay" data-nav-toggler data-overlay></div>

    </div>
</header>
<!--
   - custom js link
 -->
<script src="./assets/js/script.js" defer></script>

<!--
  - ionicon link
-->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>