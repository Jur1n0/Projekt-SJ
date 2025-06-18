<!DOCTYPE html>
<html lang="en">

<?php include("head.php") ?>

<body id="top">

<?php include("header.php") ?>

<main>
    <article>
        <section class="section hero" id="home" aria-label="home">
            <div class="container">
                <p class="hero-subtitle">Ušetrite čas a lietajte v luxuse</p>
                <h1 class="h1 hero-title">Luxury Jet Flights</h1>
                <div class="btn-wrapper">
                    <a href="#offer" class="btn btn-primary">Objednať teraz</a>
                    <a href="#about" class="btn btn-secondary">Čítať viac</a>
                </div>
                <img src="../assets/images/hero-banner.png" width="1474" height="426" alt="airplane" class="abs-img">
            </div>
        </section>

        <section class="section service" aria-label="service" id="service">
            <div class="container">
                <ul class="grid-list">
                    <li>
                        <div class="service-card">
                            <ion-icon name="diamond-outline" aria-hidden="true"></ion-icon>
                            <h3 class="h3">
                                <a class="card-title">Luxus a komfort</a>
                            </h3>
                        </div>
                    </li>

                    <li>
                        <div class="service-card">
                            <ion-icon name="shield-checkmark-outline" aria-hidden="true"></ion-icon>
                            <h3 class="h3">
                                <a class="card-title">Bezpečnosť</a>
                            </h3>
                        </div>
                    </li>

                    <li>
                        <div class="service-card">
                            <ion-icon name="calendar-outline" aria-hidden="true"></ion-icon>
                            <h3 class="h3">
                                <a class="card-title">Osobný plán</a>
                            </h3>
                        </div>
                    </li>

                    <li>
                        <div class="service-card">
                            <ion-icon name="business-outline" aria-hidden="true"></ion-icon>
                            <h3 class="h3">
                                <a class="card-title">Mnoho destinácií</a>
                            </h3>
                        </div>
                    </li>
                </ul>
            </div>
        </section>

        <section class="section about" id="about" aria-label="about">
            <div class="container">
                <div class="about-content">
                    <p class="section-subtitle">Spoznajte nás</p>
                    <h2 class="h2 section-title">Lietajte s nami v luxuse a šetrite čas a peniaze</h2>
                    <p class="section-text">
                        Spoločnosť Fly je synonymom pre exkluzivitu a pohodlie v súkromnom letectve. Ponúkame bezkonkurenčný zážitok z lietania, kde sa luxus spája s efektivitou a diskrétnosťou. Či už cestujete za biznisom alebo oddychom, Fly vám zaručuje plynulú cestu, prispôsobenú vašim individuálnym potrebám. S nami je každý let zážitkom.
                    </p>
                    <ul class="about-list">
                        <li class="about-list-item">
                            <ion-icon name="checkmark-outline" aria-hidden="ture"></ion-icon>
                            <p class="item-text">Dlhoročné skúsenosti v oblasti letectva a služieb</p>
                        </li>

                        <li class="about-list-item">
                            <ion-icon name="checkmark-outline" aria-hidden="ture"></ion-icon>
                            <p class="item-text">Rýchle, bezpečné a príjemné lety Vás dostanú kamkoľvek</p>
                        </li>
                    </ul>
                    <a href="#offer" class="btn btn-primary">Objednať teraz</a>
                </div>
                <figure class="about-banner img-holder" style="--width: 470; --height: 550;">
                    <img src="../assets/images/about-banner.jpg" width="470" height="550" loading="lazy" alt="about banner"
                         class="img-cover">
                </figure>
            </div>
        </section>

        <?php
        require_once '../module/Database.php';
        require_once '../classes/News.php';

        $db = new Database();
        $pdo_conn = $db->getConnection();
        $news_obj = new News($pdo_conn);

        $latest_news = [];
        try {
            $stmt = $pdo_conn->prepare("SELECT idNews, Nadpis, Text, Obrazok, created_at FROM news ORDER BY created_at DESC LIMIT 4");
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $latest_news[] = $row;
            }
        } catch (Exception $e) {
            error_log("Error fetching latest news on index.php: " . $e->getMessage());
            // Môžete tu pridať aj užívateľsky prívetivú správu, ak je to potrebné
        }
        ?>

        <section class="section news" aria-label="news" id="news">
            <div class="container">
                <p class="section-subtitle">Najnovšie aktualizácie</p>
                <h2 class="h2 section-title">Naše novinky</h2>

                <?php if (!empty($latest_news)): ?>
                    <ul class="grid-list">
                        <?php foreach ($latest_news as $news_item): ?>
                            <li>
                                <div class="news-card">
                                    <?php if (!empty($news_item['Obrazok'])): ?>
                                        <figure class="card-banner img-holder" style="--width: 416; --height: 250;">
                                            <img src="<?php echo htmlspecialchars($news_item['Obrazok']); ?>" width="416" height="250" loading="lazy"
                                                 alt="<?php echo htmlspecialchars($news_item['Nadpis']); ?>" class="img-cover">
                                        </figure>
                                    <?php else: ?>
                                        <figure class="card-banner img-holder" style="--width: 416; --height: 250;">
                                            <img src="../assets/images/logo.svg" width="416" height="250" loading="lazy"
                                                 alt="No image available" class="img-cover">
                                        </figure>
                                    <?php endif; ?>

                                    <div class="card-content">
                                        <h3 class="h3">
                                            <a href="news.php?id=<?php echo htmlspecialchars($news_item['idNews']); ?>" class="card-title"><?php echo htmlspecialchars($news_item['Nadpis']); ?></a>
                                        </h3>

                                        <ul class="card-meta-list">
                                            <li class="card-meta-item">
                                                <ion-icon name="calendar-outline" aria-hidden="true"></ion-icon>

                                                <time datetime="<?php echo date('Y-m-d', strtotime($news_item['created_at'])); ?>">
                                                    <?php echo date('d M Y', strtotime($news_item['created_at'])); ?>
                                                </time>
                                            </li>
                                        </ul>

                                        <p class="card-text">
                                            <?php echo htmlspecialchars(mb_strimwidth($news_item['Text'], 0, 150, "...")); // Zobrazíme len útržok textu ?>
                                        </p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-center" style="margin-top: 50px;">
                        <a href="news.php" class="btn btn-primary">Zobraziť všetky novinky</a>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; margin-top: 30px;">Momentálne nie sú k dispozícii žiadne novinky.</p>
                <?php endif; ?>
            </div>
        </section>

        <?php
        require_once '../module/Database.php';
        require_once '../classes/Flight.php';

        $popular_flights = [];
        try {
            $db = new Database();
            $pdo_conn = $db->getConnection();
            $flight_obj = new Flight($pdo_conn);
            $stmt_popular = $flight_obj->readPopularFlights(4); // Načíta 4 najpopulárnejšie lety

            if ($stmt_popular && $stmt_popular->rowCount() > 0) {
                while($row = $stmt_popular->fetch(PDO::FETCH_ASSOC)) {
                    $popular_flights[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error fetching popular flights: " . $e->getMessage());
            // Môžeš nastaviť session message, ak chceš, aby používateľ videl chybu
            // $_SESSION['message'] = "Chyba pri načítaní populárnych letov.";
            // $_SESSION['message_type'] = "error";
        }
        ?>

        <section class="section offer" aria-label="offer" id="offer">
            <div class="container">
                <p class="section-subtitle">Najlepšie ponuky</p>
                <h2 class="h2 section-title">Najpopulárnejšie lety pre vás</h2>

                <?php if (!empty($popular_flights)): ?>
                    <ul class="grid-list">
                        <?php foreach ($popular_flights as $flight): ?>
                            <li>
                                <div class="offer-card">
                                    <figure class="card-banner img-holder" style="--width: 384; --height: 250;">
                                        <img src="<?php echo htmlspecialchars($flight['obrazok'] ?? '../assets/images/default-flight.jpg'); ?>"
                                             width="384" height="250" loading="lazy"
                                             alt="<?php echo htmlspecialchars($flight['miesto_odletu']); ?> do <?php echo htmlspecialchars($flight['miesto_priletu']); ?>"
                                             class="img-cover">
                                    </figure>
                                    <div class="card-content">
                                        <h3 class="h3 card-title">
                                            <a href="flight_detail.php?id=<?php echo htmlspecialchars($flight['id']); ?>">
                                                <?php echo htmlspecialchars($flight['miesto_odletu']); ?> &rarr; <?php echo htmlspecialchars($flight['miesto_priletu']); ?>
                                            </a>
                                        </h3>
                                        <p class="card-text">
                                            Lietadlo: <?php echo htmlspecialchars($flight['lietadlo']); ?><br>
                                            Odlet: <?php echo date('d.m.Y H:i', strtotime($flight['datum_cas_odletu'])); ?>
                                        </p>
                                        <ul class="card-meta-list">
                                            <li class="card-meta-item">
                                                <ion-icon name="pricetag-outline" aria-hidden="true"></ion-icon>
                                                <span class="span"><?php echo number_format($flight['cena'], 2, ',', ' '); ?> €</span>
                                            </li>
                                            <li class="card-meta-item">
                                                <ion-icon name="time-outline" aria-hidden="true"></ion-icon>
                                                <span class="span">
                                            <?php echo htmlspecialchars($flight['dlzka_letu_hodiny']); ?>h
                                            <?php echo htmlspecialchars($flight['dlzka_letu_minuty']); ?>m
                                        </span>
                                            </li>
                                            <li class="card-meta-item">
                                                <ion-icon name="people-outline" aria-hidden="true"></ion-icon>
                                                <span class="span">Kapacita: <?php echo htmlspecialchars($flight['kapacita_lietadla']); ?></span>
                                            </li>
                                        </ul>
                                        <a href="flight_detail.php?id=<?php echo htmlspecialchars($flight['id']); ?>" class="btn btn-primary">Rezervovať</a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Momentálne nie sú dostupné žiadne populárne lety.</p>
                <?php endif; ?>
                <div class="text-center" style="margin-top: 30px;">
                    <a href="flights.php" class="btn btn-secondary">Zobraziť všetky lety</a>
                </div>
            </div>
        </section>

        <section class="section newsletter has-bg-image"
                 style="background-image: url('../assets/images/newsletter-bg.png')" aria-label="newsletter" id="contact">
            <div class="container">
                <div>
                    <p class="section-subtitle">Subscribe Now</p>
                    <h2 class="h2 section-title">Want to know about our offers first?</h2>
                </div>
                <div>
                    <form action="" class="newsletter-form">
                        <input type="email" name="email_address" placeholder="Enter email address" class="input-field">
                        <button type="submit" class="btn btn-secondary">
                            Subscribe
                            <ion-icon name="airplane" aria-hidden="true"></ion-icon>
                        </button>
                    </form>
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