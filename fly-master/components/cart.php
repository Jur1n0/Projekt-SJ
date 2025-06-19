<?php
session_start();
require_once '../module/Database.php';
require_once '../classes/Cart.php';
require_once '../classes/Flight.php';

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

$pickup_dropoff_price_per_person = 50;
$service_prices = [
    'budget' => 0,
    'comfy' => 100,
    'luxury' => 200,
];

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $cart_obj = new Cart($pdo_conn);
    $flight_obj = new Flight($pdo_conn);

    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['item_id'])) {
        $item_id = filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT);
        if ($item_id) {
            $cart_obj->id = $item_id;
            $cart_obj->user_id = $user_id;

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
        header("Location: cart.php");
        exit();
    }

    $stmt = $cart_obj->readByUserId($user_id);
    if ($stmt && $stmt->rowCount() > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
                $row['flight_details'] = null;
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
<body id="top">
<?php include("header.php") ?>
<script src="../assets/js/script.js"></script>
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
                                if (!$item['flight_details']) continue;
                                $flight = $item['flight_details'];

                                $current_service_package = $item['service_package'] ?? 'budget';
                                $current_pickup_service = $item['pickup_service'] ?? 0;
                                $current_dropoff_service = $item['dropoff_service'] ?? 0;
                                $current_notes = $item['notes'] ?? '';

                                $initial_item_price = $item['price_at_addition'];

                                $calculated_initial_price = $flight['cena'];
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
</body>
<?php include("footer.php") ?>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script noModule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</html>