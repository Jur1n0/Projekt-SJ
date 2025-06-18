<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Flight.php';
require_once '../classes/Cart.php';
require_once '../module/FlightFilter.php'; // Zahrnieme FlightFilter

// Získanie filtrovacích a triediacich parametrov z GET
// Použijeme triedu FlightFilter na spracovanie parametrov
$flightFilter = new FlightFilter();

$search_query = $flightFilter->getSearchQuery();
$miesto_odletu = $flightFilter->getMiestoOdletu();
$miesto_priletu = $flightFilter->getMiestoPriletu();
$min_capacity = $flightFilter->getMinCapacity();
$max_capacity = $flightFilter->getMaxCapacity();
$min_price = $flightFilter->getMinPrice();
$max_price = $flightFilter->getMaxPrice();
$date_from = $flightFilter->getDateFrom();
$date_to = $flightFilter->getDateTo();
$sort_by = $flightFilter->getSortBy();
$sort_order = $flightFilter->getSortOrder();

$flights = [];
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $flight_obj = new Flight($pdo_conn);
    $stmt = $flight_obj->readFilteredAndSorted(
        $search_query, $miesto_odletu, $miesto_priletu,
        $min_capacity, $max_capacity, $min_price, $max_price,
        $date_from, $date_to, $sort_by, $sort_order
    );

    if ($stmt && $stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $flights[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Chyba pri načítaní letov: " . $e->getMessage());
    $_SESSION['message'] = "Chyba pri načítaní letov.";
    $_SESSION['message_type'] = "error";
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<body id="top">

<?php include("header.php") ?>

<main>
    <article>
        <section class="section flights-page" aria-label="flights">
            <div class="container">
                <p class="section-subtitle">Naše Lety</p>
                <h2 class="h2 section-title">Dostupné lety</h2>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="message <?php echo htmlspecialchars($_SESSION['message_type']); ?>">
                        <?php echo htmlspecialchars($_SESSION['message']); ?>
                    </div>
                    <?php
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>

                <div class="filter-section">
                    <button class="btn btn-primary" onclick="openFilterModal()">Filter a Zoradenie</button>
                </div>

                <ul class="grid-list">
                    <?php if (!empty($flights)): ?>
                        <?php foreach ($flights as $flight): ?>
                            <li>
                                <div class="offer-card flight-card">
                                    <div class="card-content">
                                        <div class="card-header">
                                            <div class="text-group">
                                                <p class="card-subtitle">
                                                    <ion-icon name="airplane-outline"></ion-icon>
                                                    <span><?php echo htmlspecialchars($flight['lietadlo']); ?></span>
                                                </p>
                                                <h3 class="h3 card-title">
                                                    <a href="flight_detail.php?id=<?php echo htmlspecialchars($flight['id']); ?>">
                                                        <?php echo htmlspecialchars($flight['miesto_odletu']); ?>
                                                        <ion-icon name="arrow-forward-outline"></ion-icon>
                                                        <?php echo htmlspecialchars($flight['miesto_priletu']); ?>
                                                    </a>
                                                </h3>
                                            </div>
                                            <div class="card-price">
                                                <p>Cena za lietadlo</p>
                                                <data value="<?php echo htmlspecialchars($flight['cena']); ?>">€<?php echo number_format($flight['cena'], 0, ',', ' '); ?></data>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <p class="card-text">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                                Odlet: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_odletu']))); ?>
                                            </p>
                                            <p class="card-text">
                                                <ion-icon name="calendar-outline"></ion-icon>
                                                Prílet: <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_priletu']))); ?>
                                            </p>
                                            <p class="card-text">
                                                <ion-icon name="time-outline"></ion-icon>
                                                Dĺžka letu: <?php echo htmlspecialchars($flight['dlzka_letu_hodiny']); ?>h
                                                <?php echo htmlspecialchars($flight['dlzka_letu_minuty']); ?>m
                                            </p>
                                            <p class="card-text">
                                                <ion-icon name="people-outline"></ion-icon>
                                                Kapacita: <?php echo htmlspecialchars($flight['kapacita_lietadla']); ?> osôb
                                            </p>
                                        </div>
                                        <?php if (!empty($flight['obrazok'])): ?>
                                            <figure class="card-banner img-holder" style="--width: 330; --height: 240;">
                                                <img src="../assets/images/flights/<?php echo htmlspecialchars($flight['obrazok']); ?>"
                                                     width="330" height="240" loading="lazy"
                                                     alt="<?php echo htmlspecialchars($flight['lietadlo']); ?>"
                                                     class="img-cover">
                                            </figure>
                                        <?php endif; ?>
                                        <div class="add-to-cart-wrapper">
                                            <form action="../process/process_add_to_cart.php" method="POST" class="add-to-cart-form">
                                                <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight['id']); ?>">
                                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($flight['cena']); ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-primary">Pridať do košíka</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Momentálne nie sú k dispozícii žiadne lety, ktoré by zodpovedali vašim kritériám.</p>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    </article>
</main>

<div id="filterModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeFilterModal()">&times;</span>
        <h2>Filter a Zoradenie letov</h2>
        <form action="flights.php" method="GET">
            <h3>Filtrovať podľa:</h3>
            <div class="form-group">
                <label for="search">Hľadať (Lietadlo, Miesto odletu/príletu):</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
            </div>
            <div class="form-group">
                <label for="miesto_odletu">Miesto odletu:</label>
                <input type="text" id="miesto_odletu" name="miesto_odletu" value="<?php echo htmlspecialchars($miesto_odletu); ?>">
            </div>
            <div class="form-group">
                <label for="miesto_priletu">Miesto príletu:</label>
                <input type="text" id="miesto_priletu" name="miesto_priletu" value="<?php echo htmlspecialchars($miesto_priletu); ?>">
            </div>
            <div class="form-group">
                <label for="min_capacity">Minimálna kapacita:</label>
                <input type="number" id="min_capacity" name="min_capacity" value="<?php echo htmlspecialchars($min_capacity ?? ''); ?>" min="1">
            </div>
            <div class="form-group">
                <label for="max_capacity">Maximálna kapacita:</label>
                <input type="number" id="max_capacity" name="max_capacity" value="<?php echo htmlspecialchars($max_capacity ?? ''); ?>" min="1">
            </div>
            <div class="form-group">
                <label for="min_price">Minimálna cena:</label>
                <input type="number" id="min_price" name="min_price" value="<?php echo htmlspecialchars($min_price ?? ''); ?>" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label for="max_price">Maximálna cena:</label>
                <input type="number" id="max_price" name="max_price" value="<?php echo htmlspecialchars($max_price ?? ''); ?>" step="0.01" min="0">
            </div>
            <div class="form-group">
                <label for="date_from">Dátum odletu od:</label>
                <input type="date" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
            </div>
            <div class="form-group">
                <label for="date_to">Dátum odletu do:</label>
                <input type="date" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
            </div>

            <h3>Zoradiť podľa:</h3>
            <div class="form-group">
                <label for="sort_by">Zoradiť podľa:</label>
                <select id="sort_by" name="sort_by">
                    <option value="datum_cas_odletu" <?php echo ($sort_by === 'datum_cas_odletu') ? 'selected' : ''; ?>>Dátum odletu</option>
                    <option value="cena" <?php echo ($sort_by === 'cena') ? 'selected' : ''; ?>>Cena</option>
                    <option value="order_count" <?php echo ($sort_by === 'order_count') ? 'selected' : ''; ?>>Popularita</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Poradie:</label>
                <select id="sort_order" name="sort_order">
                    <option value="ASC" <?php echo ($sort_order === 'ASC') ? 'selected' : ''; ?>>Vzostupne</option>
                    <option value="DESC" <?php echo ($sort_order === 'DESC') ? 'selected' : ''; ?>>Zostupne</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Použiť Filter a Zoradenie</button>
            <a href="flights.php" class="btn btn-secondary">Zrušiť Filter</a>
        </form>
    </div>
</div>

<?php include("footer.php") ?>

<script>
    // Funkcie pre modálne okno filtra
    function openFilterModal() {
        document.getElementById('filterModal').classList.add('show');
    }

    function closeFilterModal() {
        document.getElementById('filterModal').classList.remove('show');
    }

    // Zatvorenie modálneho okna kliknutím mimo neho
    window.onclick = function(event) {
        const filterModal = document.getElementById('filterModal');
        if (event.target === filterModal) {
            filterModal.classList.remove('show');
        }
    }
</script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>