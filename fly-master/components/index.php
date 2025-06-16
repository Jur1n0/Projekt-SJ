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

        <section class="offer" aria-label="offer" id="offer">
            <div class="offer-content section" style="background-image: url('../assets/images/offer-bg.png')">
                <div class="container">
                    <p class="section-subtitle">Special Offer</p>
                    <h2 class="h2 section-title">The best service for business people who appreciate time</h2>
                    <p class="section-text">
                        Non augue egestas, commodo velit eget, vestibulum tellus. Curabitur vulputate justo elit, at elementum
                        pulvinar.
                        Pellentesque habitant morbi tristique.
                    </p>
                    <a href="#" class="btn btn-primary">Discover More</a>
                </div>
            </div>
            <div class="offer-banner has-bg-image" style="background-image: url('../assets/images/offer-banner.jpg')"></div>
        </section>

        <section class="section flight" aria-label="privet flight">
            <div class="container">
                <ul class="grid-list">
                    <li>
                        <div class="flight-content">
                            <p class="section-subtitle">Private Flights</p>
                            <h2 class="h2 section-title">Browse legs of our charters</h2>
                            <p class="section-text">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur condimentum, lacus non faucibus
                                congue, lectus quam
                                viverra nulla, quis egestas neque sapien ac magna.
                            </p>
                        </div>
                    </li>

                    <li>
                        <div class="flight-card">
                            <h3 class="card-title">
                                New York
                                <ion-icon name="airplane" aria-hidden="true"></ion-icon>
                                Moscow
                            </h3>
                            <div class="card-banner">
                                <img src="../assets/images/flight-1.png" width="263" height="84" loading="lazy"
                                     alt="new york to moscow flight airplane" class="w-100">
                            </div>
                            <ul class="card-list">
                                <li class="card-item">
                                    <span class="span">Date:</span>
                                    Tuesday, Jul 6, 2022
                                </li>

                                <li class="card-item">
                                    <span class="span">Departure:</span>
                                    11:25 pm
                                </li>

                                <li class="card-item">
                                    <span class="span">Arrival:</span>
                                    02:25 am
                                </li>

                                <li class="card-item">
                                    <span class="span">Starting From:</span>
                                    $2786
                                </li>

                                <li class="card-item">
                                    <span class="span">Person:</span>
                                    Adult 3
                                </li>
                            </ul>
                            <a href="#" class="btn btn-primary">Book Now</a>
                        </div>
                    </li>

                    <li>
                        <div class="flight-card">
                            <h3 class="card-title">
                                New York
                                <ion-icon name="airplane" aria-hidden="true"></ion-icon>
                                Moscow
                            </h3>
                            <div class="card-banner">
                                <img src="../assets/images/flight-1.png" width="263" height="84" loading="lazy"
                                     alt="new york to moscow flight airplane" class="w-100">
                            </div>
                            <ul class="card-list">
                                <li class="card-item">
                                    <span class="span">Date:</span>
                                    Tuesday, Jul 6, 2022
                                </li>

                                <li class="card-item">
                                    <span class="span">Departure:</span>
                                    11:25 pm
                                </li>

                                <li class="card-item">
                                    <span class="span">Arrival:</span>
                                    02:25 am
                                </li>

                                <li class="card-item">
                                    <span class="span">Starting From:</span>
                                    $2786
                                </li>

                                <li class="card-item">
                                    <span class="span">Person:</span>
                                    Adult 3
                                </li>
                            </ul>
                            <a href="#" class="btn btn-primary">Book Now</a>
                        </div>
                    </li>
                </ul>
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