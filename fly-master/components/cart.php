<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Cart.php';
require_once '../classes/Flight.php'; // Potrebné pre detaily letu, ale nie pre kontrolu kapacity

$cart_items = [];
$total_cart_price = 0;
$message = '';
$message_type = '';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Pre zobrazenie košíka sa musíte prihlásiť.";
    $_SESSION['message_type'] = "info";
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Definovanie cien pre služby a odvoz
$pickup_dropoff_price_per_person = 50; // Cena za osobu, budeme násobiť kapacitou letu
$service_prices = [
    'budget' => 0,
    'comfy' => 100,
    'luxury' => 200,
];

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);
    $flight_obj = new Flight($pdo_conn); // Instance Flight triedy pre zobrazenie detailov

    // Spracovanie správy zo session (napr. po pridaní do košíka)
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }

    // Spracovanie akcie z košíka (GET request pre delete)
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['item_id'])) {
        $item_id = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
        if ($item_id) {
            $cart_obj->id = $item_id;
            $cart_obj->user_id = $user_id; // Zabezpečenie, že používateľ môže vymazať iba svoje položky

            if ($cart_obj->deleteCartItem()) {
                $_SESSION['message'] = "Položka bola úspešne odstránená z košíka.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Chyba pri odstraňovaní položky z košíka.";
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "Neplatné ID položky pre odstránenie.";
            $_SESSION['message_type'] = "error";
        }
        header("Location: cart.php"); // Presmerovať, aby sa zabránilo opätovnému odoslaniu formulára
        exit();
    }

    // Načítanie položiek košíka s detailmi letov
    $stmt = $cart_obj->readByUserId($user_id);
    if ($stmt && $stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Získame detaily letu pre každú položku
            $flight_obj->id = $row['flight_id'];
            if ($flight_obj->readOne()) {
                $row['flight_details'] = [
                    'lietadlo' => $flight_obj->lietadlo,
                    'miesto_odletu' => $flight_obj->miesto_odletu,
                    'miesto_priletu' => $flight_obj->miesto_priletu,
                    'datum_cas_odletu' => $flight_obj->datum_cas_odletu,
                    'datum_cas_priletu' => $flight_obj->datum_cas_priletu,
                    'kapacita_lietadla' => $flight_obj->kapacita_lietadla,
                    'cena' => $flight_obj->cena, // Základná cena letu
                ];
            } else {
                $row['flight_details'] = null; // Let nebol nájdený
            }
            $cart_items[] = $row;
        }
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní košíka: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Cart fetch error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<?php include("head.php") ?>
<style>
    /* Základné štýly pre tabuľku košíka */
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

    .cart-table .form-group {
        margin-bottom: 0; /* Odstráni medzeru pod formulárovými prvkami v tabuľke */
    }

    .cart-table input[type="checkbox"],
    .cart-table input[type="radio"] {
        margin-right: 5px;
        transform: scale(1.2); /* Zväčší checkbox/radio button */
    }

    .cart-table label {
        display: inline-block;
        margin-right: 15px;
        font-weight: normal;
    }

    .cart-table select {
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        background-color: var(--background-primary);
        color: var(--text-color);
        width: 100%; /* Vyplní stĺpec */
    }

    .cart-table textarea {
        width: calc(100% - 16px); /* Zohľadni padding */
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        background-color: var(--background-primary);
        color: var(--text-color);
        min-height: 60px; /* Aby bolo vidno viac riadkov */
        resize: vertical; /* Umožní vertikálne zmenu veľkosti */
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

    .checkout-buttons {
        margin-top: 30px;
        text-align: right;
    }

    .checkout-buttons .btn {
        padding: 12px 25px;
        font-size: 1.1rem;
        margin-left: 15px;
    }

    .delete-item-btn {
        background-color: var(--red);
        color: var(--white);
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .delete-item-btn:hover {
        background-color: #c82333;
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
        <section class="section cart-page" aria-label="cart">
            <div class="container">
                <p class="section-subtitle">Váš Košík</p>
                <h2 class="h2 section-title">Položky v košíku</h2>

                <?php if (!empty($message)): ?>
                    <div class="message <?php echo htmlspecialchars($message_type); ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($cart_items)): ?>
                    <form action="../process/process_update_cart_items.php" method="POST" id="cart-form">
                        <table class="cart-table">
                            <thead>
                            <tr>
                                <th>Lietadlo</th>
                                <th>Trasa</th>
                                <th>Odlet</th>
                                <th>Prílet</th>
                                <th>Kapacita</th>
                                <th>Základná cena</th>
                                <th>Servisný balík</th>
                                <th>Odvoz na letisko</th>
                                <th>Odvoz z letiska</th>
                                <th>Poznámky</th>
                                <th>Celková cena</th>
                                <th>Akcie</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cart_items as $item):
                                if (!$item['flight_details']) continue; // Ak sa nenašli detaily letu, preskočíme
                                $flight = $item['flight_details'];

                                // Predvolené nastavenia z DB alebo predvolené hodnoty
                                $current_service_package = $item['service_package'] ?? 'budget';
                                $current_pickup_service = $item['pickup_service'] ?? 0;
                                $current_dropoff_service = $item['dropoff_service'] ?? 0;
                                $current_notes = $item['notes'] ?? '';

                                // Pôvodná cena z databázy (s už započítanými službami)
                                // Túto hodnotu budeme na začiatku zobrazovať a pracovať s ňou v JS
                                $initial_item_price = $item['price_at_addition'];

                                // Výpočet počiatočnej ceny s aktuálnymi službami z databázy (pre zobrazenie)
                                $calculated_initial_price = $flight['cena']; // Základná cena letu
                                $calculated_initial_price += $service_prices[$current_service_package] * $flight['kapacita_lietadla'];
                                if ($current_pickup_service) {
                                    $calculated_initial_price += $pickup_dropoff_price_per_person * $flight['kapacita_lietadla'];
                                }
                                if ($current_dropoff_service) {
                                    $calculated_initial_price += $pickup_dropoff_price_per_person * $flight['kapacita_lietadla'];
                                }

                                ?>
                                <tr data-cart-item-id="<?php echo htmlspecialchars($item['id']); ?>"
                                    data-flight-id="<?php echo htmlspecialchars($item['flight_id']); ?>"
                                    data-base-price="<?php echo htmlspecialchars($flight['cena']); ?>"
                                    data-capacity="<?php echo htmlspecialchars($flight['kapacita_lietadla']); ?>">
                                    <td><?php echo htmlspecialchars($flight['lietadlo']); ?></td>
                                    <td><?php echo htmlspecialchars($flight['miesto_odletu']); ?> &rarr; <?php echo htmlspecialchars($flight['miesto_priletu']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_odletu']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_priletu']))); ?></td>
                                    <td><?php echo htmlspecialchars($flight['kapacita_lietadla']); ?> osôb</td>
                                    <td>€<?php echo number_format($flight['cena'], 0, ',', ' '); ?></td>
                                    <td>
                                        <div class="form-group">
                                            <select name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][service_package]" class="service-package-select">
                                                <option value="budget" data-price="0" <?php echo ($current_service_package === 'budget' ? 'selected' : ''); ?>>Budget (€0)</option>
                                                <option value="comfy" data-price="100" <?php echo ($current_service_package === 'comfy' ? 'selected' : ''); ?>>Comfy (€100)</option>
                                                <option value="luxury" data-price="200" <?php echo ($current_service_package === 'luxury' ? 'selected' : ''); ?>>Luxury (€200)</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type="checkbox" name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][pickup_service]" class="pickup-service-checkbox" value="1" <?php echo ($current_pickup_service ? 'checked' : ''); ?>>
                                            <label for="pickup_service_<?php echo htmlspecialchars($item['id']); ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <input type="checkbox" name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][dropoff_service]" class="dropoff-service-checkbox" value="1" <?php echo ($current_dropoff_service ? 'checked' : ''); ?>>
                                            <label for="dropoff_service_<?php echo htmlspecialchars($item['id']); ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <textarea name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][notes]" placeholder="Špeciálne požiadavky"><?php echo htmlspecialchars($current_notes); ?></textarea>
                                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][initial_price]" value="<?php echo htmlspecialchars($flight['cena']); ?>">
                                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][capacity]" value="<?php echo htmlspecialchars($flight['kapacita_lietadla']); ?>">
                                            <input type="hidden" name="cart_items[<?php echo htmlspecialchars($item['id']); ?>][new_calculated_price]" id="new_calculated_price_<?php echo htmlspecialchars($item['id']); ?>" value="<?php echo htmlspecialchars($calculated_initial_price); ?>">
                                        </div>
                                    </td>
                                    <td class="item-total-price">€<?php echo number_format($calculated_initial_price, 0, ',', ' '); ?></td>
                                    <td>
                                        <button type="button" class="delete-item-btn" onclick="confirmDeleteItem(<?php echo htmlspecialchars($item['id']); ?>)">Zmazať</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                            <tr class="cart-total-row">
                                <td colspan="10">Celková suma košíka:</td>
                                <td class="total-amount" id="total-cart-price">€<?php echo number_format($total_cart_price, 0, ',', ' '); ?></td>
                                <td></td>
                            </tr>
                            </tfoot>
                        </table>

                        <div class="checkout-buttons">
                            <button type="submit" class="btn btn-secondary">Aktualizovať košík</button>
                            <a href="checkout.php" class="btn btn-primary">Prejsť k platbe</a>
                        </div>
                    </form>
                <?php else: ?>
                    <p>Váš košík je prázdny. <a href="flights.php">Prejdite na lety a pridajte si nejaké.</a></p>
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
    const pickupDropoffPricePerPerson = <?php echo json_encode($pickup_dropoff_price_per_person); ?>;
    const servicePrices = <?php echo json_encode($service_prices); ?>;

    function updateItemPrice(element) {
        const row = element.closest('tr');
        if (!row) return; // Ak element nie je v riadku tabuľky, nič nerobíme

        const cartItemId = row.dataset.cartItemId;
        const basePrice = parseFloat(row.dataset.basePrice);
        const capacity = parseInt(row.dataset.capacity);

        const servicePackageSelect = row.querySelector('.service-package-select');
        const pickupServiceCheckbox = row.querySelector('.pickup-service-checkbox');
        const dropoffServiceCheckbox = row.querySelector('.dropoff-service-checkbox');
        const itemTotalPriceElement = row.querySelector('.item-total-price');
        const hiddenCalculatedPriceInput = document.getElementById(`new_calculated_price_${cartItemId}`);

        let currentItemPrice = basePrice;

        // Cena za servisný balík
        const selectedPackage = servicePackageSelect.value;
        const packagePrice = servicePrices[selectedPackage] || 0;
        currentItemPrice += packagePrice * capacity;

        // Cena za odvoz na letisko
        if (pickupServiceCheckbox.checked) {
            currentItemPrice += pickupDropoffPricePerPerson * capacity;
        }

        // Cena za odvoz z letiska
        if (dropoffServiceCheckbox.checked) {
            currentItemPrice += pickupDropoffPricePerPerson * capacity;
        }

        itemTotalPriceElement.textContent = '€' + currentItemPrice.toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        hiddenCalculatedPriceInput.value = currentItemPrice.toFixed(2); // Uložíme presnú cenu
        updateTotalCartPrice();
    }

    function updateTotalCartPrice() {
        let totalCartPrice = 0;
        document.querySelectorAll('.item-total-price').forEach(itemPriceElement => {
            // Extrahujeme číslo z textu, odstránime € a medzery
            const priceText = itemPriceElement.textContent.replace('€', '').replace(/\s/g, '').replace(',', '.');
            totalCartPrice += parseFloat(priceText);
        });
        document.getElementById('total-cart-price').textContent = '€' + totalCartPrice.toLocaleString('sk-SK', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    // Pridáme event listenery pre všetky relevantné prvky
    document.querySelectorAll('.service-package-select').forEach(select => {
        select.addEventListener('change', () => updateItemPrice(select));
    });

    document.querySelectorAll('.pickup-service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => updateItemPrice(checkbox));
    });

    document.querySelectorAll('.dropoff-service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', () => updateItemPrice(checkbox));
    });

    // Funkcia na potvrdenie zmazania položky
    function confirmDeleteItem(itemId) {
        if (confirm("Naozaj chcete odstrániť tento let z košíka?")) {
            window.location.href = 'cart.php?action=delete&item_id=' + itemId;
        }
    }

    // Inicializácia cien pri načítaní stránky
    document.addEventListener('DOMContentLoaded', () => {
        // Prejdeme všetky riadky a zavoláme updateItemPrice pre každý, aby sa ceny inicializovali
        document.querySelectorAll('tr[data-cart-item-id]').forEach(row => {
            updateItemPrice(row); // Zavoláme updateItemPrice na samotnom riadku
        });
        updateTotalCartPrice(); // Prepočíta celkovú sumu po načítaní
    });

    // Pridáme event listener pre textarea, aby sa aktualizovali hidden inputy aj pri zmene poznámok
    // Toto je dôležité, aby sa poznámky uložili aj pri aktualizácii košíka
    document.querySelectorAll('textarea[name^="cart_items"]').forEach(textarea => {
        textarea.addEventListener('input', () => {
            const row = textarea.closest('tr');
            const cartItemId = row.dataset.cartItemId;
            // Poznámky sa uložia priamo cez name atribút vo formulári, nemusíme ich dávať do hidden inputu zvlášť
        });
    });

</script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>