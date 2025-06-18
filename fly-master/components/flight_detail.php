<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Flight.php';
require_once '../classes/Cart.php';

$flight_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$flight_details = null;
$message = '';
$message_type = '';

// Spracovanie správy zo session (napr. po pridaní do košíka)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

if (!$flight_id) {
    $_SESSION['message'] = "Neplatné ID letu.";
    $_SESSION['message_type'] = "error";
    header("Location: flights.php");
    exit();
}

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $flight_obj = new Flight($pdo_conn);
    $flight_obj->id = $flight_id;

    if ($flight_obj->readOne()) {
        $flight_details = [
            'id' => $flight_obj->id,
            'lietadlo' => $flight_obj->lietadlo,
            'miesto_odletu' => $flight_obj->miesto_odletu,
            'miesto_priletu' => $flight_obj->miesto_priletu,
            'datum_cas_odletu' => $flight_obj->datum_cas_odletu,
            'datum_cas_priletu' => $flight_obj->datum_cas_priletu,
            'cena' => $flight_obj->cena,
            'kapacita_lietadla' => $flight_obj->kapacita_lietadla,
            'dlzka_letu_hodiny' => $flight_obj->dlzka_letu_hodiny,
            'dlzka_letu_minuty' => $flight_obj->dlzka_letu_minuty,
            'obrazok' => $flight_obj->obrazok,
        ];
    } else {
        $_SESSION['message'] = "Let s ID " . htmlspecialchars($flight_id) . " sa nenašiel.";
        $_SESSION['message_type'] = "error";
        header("Location: flights.php");
        exit();
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní detailov letu: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Flight detail fetch error: " . $e->getMessage());
    header("Location: flights.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<body>

<?php include("header.php") ?>

<main>
    <article>
        <section class="section flight-detail-page" aria-label="flight detail">
            <div class="container">
                <p class="section-subtitle">Detaily Letu</p>
                <h2 class="h2 section-title">Informácie o lete</h2>

                <?php if (!empty($message)): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($flight_details): ?>
                    <div class="flight-detail-card">
                        <?php if (!empty($flight_details['obrazok'])): ?>
                            <figure class="card-banner img-holder" style="--width: 700; --height: 400;">
                                <img src="../assets/images/flights/<?php echo htmlspecialchars($flight_details['obrazok']); ?>"
                                     width="700" height="400" loading="lazy"
                                     alt="<?php echo htmlspecialchars($flight_details['lietadlo']); ?>"
                                     class="img-cover">
                            </figure>
                        <?php endif; ?>

                        <div class="card-content">
                            <h3 class="h3 card-title"><?php echo htmlspecialchars($flight_details['lietadlo']); ?></h3>
                            <div class="detail-group">
                                <p><strong>Miesto odletu:</strong> <?php echo htmlspecialchars($flight_details['miesto_odletu']); ?></p>
                                <p><strong>Miesto príletu:</strong> <?php echo htmlspecialchars($flight_details['miesto_priletu']); ?></p>
                                <p><strong>Dátum a čas odletu:</strong> <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight_details['datum_cas_odletu']))); ?></p>
                                <p><strong>Dátum a čas príletu:</strong> <?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight_details['datum_cas_priletu']))); ?></p>
                                <p><strong>Dĺžka letu:</strong> <?php echo htmlspecialchars($flight_details['dlzka_letu_hodiny']); ?> hodín <?php echo htmlspecialchars($flight_details['dlzka_letu_minuty']); ?> minút</p>
                                <p><strong>Kapacita lietadla:</strong> <?php echo htmlspecialchars($flight_details['kapacita_lietadla']); ?> osôb</p>
                                <p class="flight-price"><strong>Cena za celé lietadlo:</strong> €<?php echo number_format($flight_details['cena'], 0, ',', ' '); ?></p>
                            </div>

                            <div class="add-to-cart-wrapper">
                                <form action="../process/process_add_to_cart.php" method="POST" class="add-to-cart-form">
                                    <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight_details['id']); ?>">
                                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($flight_details['cena']); ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">Pridať do košíka</button>
                                    <a href="flights.php" class="btn btn-secondary">Späť na lety</a>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php else: ?>
                    <p>Detaily letu nie sú dostupné.</p>
                    <div class="text-center" style="margin-top: 30px;">
                        <a href="flights.php" class="btn btn-secondary">Späť na lety</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </article>
</main>

<?php include("footer.php") ?>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>