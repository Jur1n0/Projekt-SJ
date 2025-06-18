<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Cart.php';
require_once '../classes/Flight.php';
require_once '../classes/Sale.php';

$cart_items = [];
$total_cart_price = 0;
$message = '';
$message_type = '';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Pre dokončenie objednávky sa musíte prihlásiť.";
    $_SESSION['message_type'] = "info";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);
    $flight_obj = new Flight($pdo_conn); // Pre získanie detailov letu pri zobrazení
    $sale_obj = new Sale($pdo_conn);

    // Načítanie položiek košíka
    $stmt = $cart_obj->readByUserId($user_id);
    if ($stmt && $stmt->rowCount() > 0) {
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Pre každú položku v košíku získame detaily letu
            $flight_obj->id = $row['flight_id'];
            if ($flight_obj->readOne()) {
                $row['flight_details'] = [
                    'lietadlo' => $flight_obj->lietadlo,
                    'miesto_odletu' => $flight_obj->miesto_odletu,
                    'miesto_priletu' => $flight_obj->miesto_priletu,
                    'datum_cas_odletu' => $flight_obj->datum_cas_odletu,
                    'datum_cas_priletu' => $flight_obj->datum_cas_priletu,
                    'kapacita_lietadla' => $flight_obj->kapacita_lietadla,
                    'cena' => $flight_obj->cena,
                ];
            } else {
                $row['flight_details'] = null; // Let nebol nájdený
            }
            $cart_items[] = $row;
            $total_cart_price += $row['price_at_addition']; // Súčet už vypočítaných cien z košíka
        }
    } else {
        $_SESSION['message'] = "Váš košík je prázdny. Nemôžete prejsť k platbe.";
        $_SESSION['message_type'] = "warning";
        header("Location: cart.php");
        exit();
    }

    // Spracovanie správ
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní košíka pre checkout: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Checkout cart fetch error: " . $e->getMessage());
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<style>
    /* Základné štýly pre tabuľku košíka (môžu byť rovnaké ako v cart.php) */
    .cart-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .cart-table th, .cart-table td {
        border: 1px solid var(--border-color);
        padding: 12px;
        text-align: left;
        vertical-align: middle;
        color: var(--text-color);
    }

    .cart-table th {
        background-color: var(--background-secondary);
        font-weight: bold;
        color: var(--heading-color);
    }

    .cart-table tr:nth-child(even) {
        background-color: var(--background-light);
    }

    .cart-table tr:hover {
        background-color: var(--background-hover);
    }

    .cart-total-row {
        font-weight: bold;
        background-color: var(--background-secondary);
    }

    .cart-total-row td {
        text-align: right;
    }

    .cart-total-row .total-amount {
        font-size: 1.2em;
        color: var(--orange-color);
    }

    .checkout-form-container {
        background-color: var(--background-primary);
        padding: 30px;
        border-radius: 10px;
        box-shadow: var(--shadow-2);
        margin-top: 30px;
    }

    .checkout-form-container h3 {
        color: var(--heading-color);
        margin-bottom: 20px;
        text-align: center;
    }

    .checkout-form-container .form-group {
        margin-bottom: 20px;
    }

    .checkout-form-container label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: var(--text-color);
    }

    .checkout-form-container input[type="text"],
    .checkout-form-container input[type="email"],
    .checkout-form-container input[type="password"],
    .checkout-form-container input[type="number"],
    .checkout-form-container select {
        width: calc(100% - 20px); /* Adjust for padding */
        padding: 10px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        background-color: var(--background-secondary);
        color: var(--text-color);
        font-size: 1rem;
    }

    .checkout-form-container .checkbox-group {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .checkout-form-container .checkbox-group input[type="checkbox"] {
        width: auto;
        transform: scale(1.2);
    }

    .checkout-form-container .gdpr-label {
        font-weight: normal;
        margin-bottom: 0;
        cursor: pointer;
    }

    .place-order-btn {
        margin-top: 25px;
        width: 100%;
        padding: 15px;
        font-size: 1.2rem;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .place-order-btn:hover {
        opacity: 0.9;
    }

    .message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: bold;
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
        <section class="section checkout-page" aria-label="checkout">
            <div class="container">
                <p class="section-subtitle">Dokončenie objednávky</p>
                <h2 class="h2 section-title">Potvrdenie a platba</h2>

                <?php if (!empty($message)): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($cart_items)): ?>
                    <h3>Sumár objednávky:</h3>
                    <table class="cart-table">
                        <thead>
                        <tr>
                            <th>Lietadlo</th>
                            <th>Trasa</th>
                            <th>Odlet</th>
                            <th>Servisný balík</th>
                            <th>Odvoz na letisko</th>
                            <th>Odvoz z letiska</th>
                            <th>Poznámky</th>
                            <th>Cena položky</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item):
                            if (!$item['flight_details']) continue;
                            $flight = $item['flight_details'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($flight['lietadlo']); ?></td>
                                <td><?php echo htmlspecialchars($flight['miesto_odletu']); ?> &rarr; <?php echo htmlspecialchars($flight['miesto_priletu']); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_odletu']))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($item['service_package'])); ?></td>
                                <td><?php echo ($item['pickup_service'] ? 'Áno' : 'Nie'); ?></td>
                                <td><?php echo ($item['dropoff_service'] ? 'Áno' : 'Nie'); ?></td>
                                <td><?php echo htmlspecialchars($item['notes'] ?: 'N/A'); ?></td>
                                <td>€<?php echo number_format($item['price_at_addition'], 0, ',', ' '); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr class="cart-total-row">
                            <td colspan="7">Celková suma na zaplatenie:</td>
                            <td class="total-amount">€<?php echo number_format($total_cart_price, 0, ',', ' '); ?></td>
                        </tr>
                        </tfoot>
                    </table>

                    <div class="checkout-form-container">
                        <h3>Platobné údaje</h3>
                        <form action="../process/process_checkout.php" method="POST">
                            <div class="form-group">
                                <label for="payment_method">Spôsob platby:</label>
                                <select id="payment_method" name="payment_method" required>
                                    <option value="">Vyberte spôsob platby</option>
                                    <option value="card">Platobná karta (simulovaná)</option>
                                    <option value="paypal">PayPal (simulovaný)</option>
                                </select>
                            </div>

                            <div id="card_details" style="display: none;">
                                <div class="form-group">
                                    <label for="card_number">Číslo karty:</label>
                                    <input type="text" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" pattern="[0-9]{13,16}" title="Prosím zadajte platné číslo karty (13-16 číslic)" required>
                                </div>
                                <div class="form-group">
                                    <label for="card_name">Meno na karte:</label>
                                    <input type="text" id="card_name" name="card_name" placeholder="Meno Priezvisko" required>
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Dátum exspirácie (MM/RR):</label>
                                    <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/RR" pattern="(0[1-9]|1[0-2])\/?([0-9]{2})" title="Prosím zadajte platný dátum exspirácie (MM/RR)" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV:</label>
                                    <input type="text" id="cvv" name="cvv" placeholder="XXX" pattern="[0-9]{3,4}" title="Prosím zadajte platné CVV (3-4 číslice)" required>
                                </div>
                            </div>


                            <div class="form-group checkbox-group">
                                <input type="checkbox" id="gdpr_consent_checkout" name="gdpr_consent_checkout" value="1" required>
                                <label for="gdpr_consent_checkout" class="gdpr-label">Súhlasím so spracovaním osobných údajov a obchodnými podmienkami.</label>
                            </div>

                            <button type="submit" name="place_order" class="btn btn-primary place-order-btn">Dokončiť objednávku</button>
                            <a href="cart.php" class="btn btn-secondary">Späť na košík</a>
                        </form>
                    </div>
                <?php else: ?>
                    <p>Žiadne položky na dokončenie objednávky. <a href="flights.php">Prejdite na lety.</a></p>
                    <div class="text-center" style="margin-top: 30px;">
                        <a href="flights.php" class="btn btn-secondary">Prejsť na lety</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </article>
</main>

<?php include("footer.php") ?>

<script>
    // Zobrazenie/skrytie detailov karty
    document.getElementById('payment_method').addEventListener('change', function() {
        const cardDetails = document.getElementById('card_details');
        if (this.value === 'card') {
            cardDetails.style.display = 'block';
            cardDetails.querySelectorAll('input').forEach(input => input.setAttribute('required', 'required'));
        } else {
            cardDetails.style.display = 'none';
            cardDetails.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
        }
    });

    // Simulácia platby - odstránime require z kariet pre ostatné platobné metódy
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethodSelect = document.getElementById('payment_method');
        const cardDetailsInputs = document.querySelectorAll('#card_details input');

        function toggleCardRequired() {
            if (paymentMethodSelect.value === 'card') {
                cardDetailsInputs.forEach(input => input.setAttribute('required', 'required'));
                document.getElementById('card_details').style.display = 'block';
            } else {
                cardDetailsInputs.forEach(input => input.removeAttribute('required'));
                document.getElementById('card_details').style.display = 'none';
            }
        }

        paymentMethodSelect.addEventListener('change', toggleCardRequired);

        // Initial call in case the default value is 'card'
        toggleCardRequired();
    });
</script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>