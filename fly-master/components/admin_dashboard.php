<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';
require_once '../classes/News.php';
require_once '../classes/Flight.php';
require_once '../classes/Sale.php';

$users = [];
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user_obj = new User($pdo_conn);
    $stmt = $user_obj->readAll();

    if ($stmt && $stmt->rowCount() > 0) {
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní používateľov: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin dashboard user fetch error: " . $e->getMessage());
}

$all_news = [];
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $news_obj = new News($pdo_conn);
    $stmt_news = $news_obj->readAll();

    if ($stmt_news && $stmt_news->rowCount() > 0) {
        while($row = $stmt_news->fetch(PDO::FETCH_ASSOC)) {
            $all_news[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní noviniek: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin dashboard news fetch error: " . $e->getMessage());
}

$all_flights = [];
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $flight_obj = new Flight($pdo_conn);
    $stmt_flights = $flight_obj->readAll();

    if ($stmt_flights && $stmt_flights->rowCount() > 0) {
        while($row = $stmt_flights->fetch(PDO::FETCH_ASSOC)) {
            $all_flights[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní letov: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin dashboard flight fetch error: " . $e->getMessage());
}

$all_sales = [];
try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $sale_obj = new Sale($pdo_conn);
    $stmt_sales = $sale_obj->readAll();

    if ($stmt_sales && $stmt_sales->rowCount() > 0) {
        while($row = $stmt_sales->fetch(PDO::FETCH_ASSOC)) {
            $all_sales[] = $row;
        }
    }
} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní objednávok: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin dashboard sales fetch error: " . $e->getMessage());
}

$current_section = $_GET['section'] ?? 'users';
?>
<?php include("head.php") ?>
<?php include 'header.php'; ?>
<script src="../assets/js/script.js"></script>
<title>Admin Dashboard - Fly</title>
<body>
<main id="admin-dashboard-page" class="main-content">
    <div class="admin-container">
        <div class="admin-dashboard-header">
            <h2>Admin Dashboard</h2>
            <a href="logout.php" class="btn-logout">Odhlásiť sa</a>
        </div>

        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
            echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        <div class="admin-navigation">
            <button class="btn-dashboard-nav <?php echo ($current_section === 'users') ? 'active' : ''; ?>" onclick="location.href='admin_dashboard.php?section=users'">Správa používateľov</button>
            <button class="btn-dashboard-nav <?php echo ($current_section === 'news') ? 'active' : ''; ?>" onclick="location.href='admin_dashboard.php?section=news'">Správa noviniek</button>
            <button class="btn-dashboard-nav <?php echo ($current_section === 'flights') ? 'active' : ''; ?>" onclick="location.href='admin_dashboard.php?section=flights'">Správa letov</button>
            <button class="btn-dashboard-nav <?php echo ($current_section === 'orders') ? 'active' : ''; ?>" onclick="location.href='admin_dashboard.php?section=orders'">Správa objednávok</button>
        </div>

        <?php if ($current_section === 'users'): ?>
            <div class="user-management-section">
                <h3>Správa používateľov</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Meno</th>
                            <th>Priezvisko</th>
                            <th>E-mail</th>
                            <th>Rola</th>
                            <th>Akcie</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td class="actions">
                                        <button class="btn-edit" onclick="openEditModal('<?php echo $user['id']; ?>', '<?php echo htmlspecialchars($user['first_name']); ?>', '<?php echo htmlspecialchars($user['last_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['role']); ?>')">Upraviť</button>
                                        <form action="../process/process_delete_user.php" method="POST" style="display: inline-block;" onsubmit="return confirm('Naozaj chcete vymazať tohto používateľa?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-delete">Vymazať</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">Žiadni používatelia na zobrazenie.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="editUserModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeEditModal()">&times;</span>
                    <h3>Upraviť používateľa</h3>
                    <?php if (isset($_SESSION['message_modal'])): ?>
                        <p class="message <?php echo $_SESSION['message_type_modal']; ?>">
                            <?php echo htmlspecialchars($_SESSION['message_modal']); ?>
                        </p>
                        <?php
                        unset($_SESSION['message_modal']);
                        unset($_SESSION['message_type_modal']);
                        ?>
                    <?php endif; ?>
                    <form action="../process/process_update_user.php" method="POST">
                        <input type="hidden" id="editUserId" name="user_id">
                        <div class="form-group">
                            <label for="editFirstName">Meno:</label>
                            <input type="text" id="editFirstName" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editLastName">Priezvisko:</label>
                            <input type="text" id="editLastName" name="last_name" required>
                        </div>
                        <div class="form-group">
                            <label for="editEmail">E-mail:</label>
                            <input type="email" id="editEmail" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="editRole">Rola:</label>
                            <input type="text" id="editRole" name="role" required>
                        </div>
                        <button type="submit" class="btn-form-submit btn-submit">Uložiť zmeny</button>
                    </form>
                </div>
            </div>

        <?php elseif ($current_section === 'news'): ?>
            <div class="news-management-section">
                <h3>Správa noviniek</h3>

                <h4>Pridať novú novinku</h4>
                <form action="../process/process_add_news.php" method="POST" class="add-news-form">
                    <div class="form-group">
                        <label for="addNadpis">Nadpis:</label>
                        <input type="text" id="addNadpis" name="Nadpis" required>
                    </div>
                    <div class="form-group">
                        <label for="addText">Text:</label>
                        <textarea id="addText" name="Text" rows="5" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="addObrazok">URL obrázka:</label>
                        <input type="text" id="addObrazok" name="Obrazok" placeholder="napr. ../assets/images/news-1.jpg">
                    </div>
                    <button type="submit" class="btn-form-submit btn-submit">Pridať novinku</button>
                </form>

                <h4 style="margin-top: 40px;">Existujúce novinky</h4>
                <div class="table-responsive">
                    <table>
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nadpis</th>
                            <th>Text</th>
                            <th>Obrázok</th>
                            <th>Dátum vytvorenia</th>
                            <th>Akcie</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($all_news) > 0): ?>
                            <?php foreach ($all_news as $news_item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($news_item['idNews']); ?></td>
                                    <td><?php echo htmlspecialchars($news_item['Nadpis']); ?></td>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($news_item['Text'], 0, 100, "...")); ?></td> <td>
                                        <?php if (!empty($news_item['Obrazok'])): ?>
                                            <img src="<?php echo htmlspecialchars($news_item['Obrazok']); ?>" alt="Obrázok" style="width: 80px; height: auto;">
                                        <?php else: ?>
                                            Žiadny
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($news_item['created_at']); ?></td>
                                    <td class="actions">
                                        <button class="btn-edit" onclick="openEditNewsModal('<?php echo $news_item['idNews']; ?>', '<?php echo htmlspecialchars($news_item['Nadpis']); ?>', '<?php echo htmlspecialchars(str_replace(["\r\n", "\r", "\n"], '\n', $news_item['Text'])); ?>', '<?php echo htmlspecialchars($news_item['Obrazok']); ?>')">Upraviť</button>
                                        <form action="../process/process_delete_news.php" method="POST" style="display: inline-block;" onsubmit="return confirm('Naozaj chcete vymazať túto novinku?');">
                                            <input type="hidden" name="idNews" value="<?php echo $news_item['idNews']; ?>">
                                            <button type="submit" class="btn-delete">Vymazať</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">Žiadne novinky na zobrazenie.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="editNewsModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeEditNewsModal()">&times;</span>
                    <h3>Upraviť novinku</h3>
                    <?php if (isset($_SESSION['message_modal_news'])): ?>
                        <p class="message <?php echo $_SESSION['message_type_modal_news']; ?>">
                            <?php echo htmlspecialchars($_SESSION['message_modal_news']); ?>
                        </p>
                        <?php
                        unset($_SESSION['message_modal_news']);
                        unset($_SESSION['message_type_modal_news']);
                        ?>
                    <?php endif; ?>
                    <form action="../process/process_update_news.php" method="POST">
                        <input type="hidden" id="editNewsId" name="idNews">
                        <div class="form-group">
                            <label for="editNewsNadpis">Nadpis:</label>
                            <input type="text" id="editNewsNadpis" name="Nadpis" required>
                        </div>
                        <div class="form-group">
                            <label for="editNewsText">Text:</label>
                            <textarea id="editNewsText" name="Text" rows="10" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="editNewsObrazok">URL obrázka:</label>
                            <input type="text" id="editNewsObrazok" name="Obrazok">
                        </div>
                        <button type="submit" class="btn-form-submit btn-submit">Uložiť zmeny</button>
                    </form>
                </div>
            </div>

        <?php elseif ($current_section == 'flights'): ?>
            <h3>Správa letov</h3>
            <div class="table-actions">
                <button class="btn btn-primary" onclick="openAddFlightModal()">Pridať nový let</button>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Lietadlo</th>
                        <th>Odlet</th>
                        <th>Prílet</th>
                        <th>Dátum/Čas Odletu</th>
                        <th>Cena (€)</th>
                        <th>Kapacita</th>
                        <th>Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($all_flights)): ?>
                        <?php foreach ($all_flights as $flight): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($flight['id']); ?></td>
                                <td><?php echo htmlspecialchars($flight['lietadlo']); ?></td>
                                <td><?php echo htmlspecialchars($flight['miesto_odletu']); ?></td>
                                <td><?php echo htmlspecialchars($flight['miesto_priletu']); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($flight['datum_cas_odletu']))); ?></td>
                                <td><?php echo htmlspecialchars(number_format($flight['cena'], 2, ',', ' ')); ?></td>
                                <td><?php echo htmlspecialchars($flight['kapacita_lietadla']); ?></td>
                                <td class="actions">
                                    <button class="btn btn-edit" onclick="openEditFlightModal(
                                            '<?php echo htmlspecialchars($flight['id']); ?>',
                                            '<?php echo htmlspecialchars($flight['lietadlo']); ?>',
                                            '<?php echo htmlspecialchars($flight['miesto_odletu']); ?>',
                                            '<?php echo htmlspecialchars($flight['miesto_priletu']); ?>',
                                            '<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($flight['datum_cas_odletu']))); ?>',
                                            '<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($flight['datum_cas_priletu']))); ?>',
                                            '<?php echo htmlspecialchars($flight['cena']); ?>',
                                            '<?php echo htmlspecialchars($flight['kapacita_lietadla']); ?>',
                                            '<?php echo htmlspecialchars($flight['dlzka_letu_hodiny']); ?>',
                                            '<?php echo htmlspecialchars($flight['dlzka_letu_minuty']); ?>',
                                            '<?php echo htmlspecialchars($flight['obrazok'] ?? ''); ?>'
                                            )">Upraviť</button>
                                    <form action="../process/process_delete_flight.php" method="POST" style="display:inline-block;" onsubmit="return confirm('Naozaj chcete vymazať tento let?');">
                                        <input type="hidden" name="flight_id" value="<?php echo htmlspecialchars($flight['id']); ?>">
                                        <button type="submit" class="btn btn-delete">Vymazať</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8">Žiadne lety na zobrazenie.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="addFlightModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeAddFlightModal()">&times;</span>
                    <h3>Pridať nový let</h3>
                    <form action="../process/process_add_flight.php" method="POST">
                        <div class="form-group">
                            <label for="addFlightLietadlo">Lietadlo:</label>
                            <input type="text" id="addFlightLietadlo" name="lietadlo" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightMiestoOdletu">Miesto odletu:</label>
                            <input type="text" id="addFlightMiestoOdletu" name="miesto_odletu" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightMiestoPriletu">Miesto príletu:</label>
                            <input type="text" id="addFlightMiestoPriletu" name="miesto_priletu" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightDatumCasOdletu">Dátum a čas odletu:</label>
                            <input type="datetime-local" id="addFlightDatumCasOdletu" name="datum_cas_odletu" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightDatumCasPriletu">Dátum a čas príletu:</label>
                            <input type="datetime-local" id="addFlightDatumCasPriletu" name="datum_cas_priletu" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightCena">Cena (€):</label>
                            <input type="number" step="0.01" id="addFlightCena" name="cena" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightKapacita">Kapacita lietadla:</label>
                            <input type="number" id="addFlightKapacita" name="kapacita_lietadla" required>
                        </div>
                        <div class="form-group">
                            <label for="addFlightDlzkaHodiny">Dĺžka letu (hodiny):</label>
                            <input type="number" id="addFlightDlzkaHodiny" name="dlzka_letu_hodiny">
                        </div>
                        <div class="form-group">
                            <label for="addFlightDlzkaMinuty">Dĺžka letu (minúty):</label>
                            <input type="number" id="addFlightDlzkaMinuty" name="dlzka_letu_minuty">
                        </div>
                        <div class="form-group">
                            <label for="addFlightObrazok">Obrázok (URL):</label>
                            <input type="text" id="addFlightObrazok" name="obrazok" placeholder="http://example.com/flight_image.jpg">
                        </div>
                        <button type="submit" class="btn btn-primary">Pridať let</button>
                    </form>
                </div>
            </div>

            <div id="editFlightModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeEditFlightModal()">&times;</span>
                    <h3>Upraviť let</h3>
                    <form action="../process/process_update_flight.php" method="POST">
                        <input type="hidden" id="editFlightId" name="id">
                        <div class="form-group">
                            <label for="editFlightLietadlo">Lietadlo:</label>
                            <input type="text" id="editFlightLietadlo" name="lietadlo" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightMiestoOdletu">Miesto odletu:</label>
                            <input type="text" id="editFlightMiestoOdletu" name="miesto_odletu" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightMiestoPriletu">Miesto príletu:</label>
                            <input type="text" id="editFlightMiestoPriletu" name="miesto_priletu" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightDatumCasOdletu">Dátum a čas odletu:</label>
                            <input type="datetime-local" id="editFlightDatumCasOdletu" name="datum_cas_odletu" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightDatumCasPriletu">Dátum a čas príletu:</label>
                            <input type="datetime-local" id="editFlightDatumCasPriletu" name="datum_cas_priletu" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightCena">Cena (€):</label>
                            <input type="number" step="0.01" id="editFlightCena" name="cena" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightKapacita">Kapacita lietadla:</label>
                            <input type="number" id="editFlightKapacita" name="kapacita_lietadla" required>
                        </div>
                        <div class="form-group">
                            <label for="editFlightDlzkaHodiny">Dĺžka letu (hodiny):</label>
                            <input type="number" id="editFlightDlzkaHodiny" name="dlzka_letu_hodiny">
                        </div>
                        <div class="form-group">
                            <label for="editFlightDlzkaMinuty">Dĺžka letu (minúty):</label>
                            <input type="number" id="editFlightDlzkaMinuty" name="dlzka_letu_minuty">
                        </div>
                        <div class="form-group">
                            <label for="editFlightObrazok">Obrázok (URL):</label>
                            <input type="text" id="editFlightObrazok" name="obrazok" placeholder="http://example.com/flight_image.jpg">
                        </div>
                        <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
                    </form>
                </div>
            </div>

        <?php elseif ($current_section == 'orders'): ?>
            <h3>Správa objednávok</h3>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>ID Objednávky</th>
                        <th>ID Používatela</th>
                        <th>Let (Odlet - Prílet)</th>
                        <th>Dátum Objednávky</th>
                        <th>Status Platby</th>
                        <th>Status Objednávky</th>
                        <th>Balíček</th>
                        <th>Odvoz na letisko</th>
                        <th>Odvoz z letiska</th>
                        <th>Poznámky</th>
                        <th>Akcie</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($all_sales)): ?>
                        <?php foreach ($all_sales as $sale): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($sale['id_sale']); ?></td>
                                <td><?php echo htmlspecialchars($sale['user_id']); ?></td>
                                <td><?php echo htmlspecialchars($sale['miesto_odletu'] . ' - ' . $sale['miesto_priletu']); ?></td>
                                <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sale['sale_date']))); ?></td>
                                <td><?php echo htmlspecialchars($sale['payment_status']); ?></td>
                                <td><?php echo htmlspecialchars($sale['order_status']); ?></td>
                                <td><?php echo htmlspecialchars($sale['service_package']); ?></td>
                                <td><?php echo $sale['pickup_service'] ? 'Áno' : 'Nie'; ?></td>
                                <td><?php echo $sale['dropoff_service'] ? 'Áno' : 'Nie'; ?></td>
                                <td><?php echo htmlspecialchars(mb_strimwidth($sale['notes'] ?? 'N/A', 0, 50, "...")); ?></td>
                                <td class="actions">
                                    <button class="btn btn-edit" onclick="openEditSaleModal(
                                            '<?php echo htmlspecialchars($sale['id_sale']); ?>',
                                            '<?php echo htmlspecialchars($sale['payment_status']); ?>',
                                            '<?php echo htmlspecialchars($sale['order_status']); ?>'
                                            )">Upraviť</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="11">Žiadne objednávky na zobrazenie.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div id="editSaleModal" class="modal">
                <div class="modal-content">
                    <span class="close-button" onclick="closeEditSaleModal()">&times;</span>
                    <h3>Upraviť stav objednávky</h3>
                    <form action="../process/process_update_sale.php" method="POST">
                        <input type="hidden" id="editSaleId" name="id_sale">
                        <div class="form-group">
                            <label for="editSalePaymentStatus">Status Platby:</label>
                            <select id="editSalePaymentStatus" name="payment_status" required>
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="editSaleOrderStatus">Status Objednávky:</label>
                            <select id="editSaleOrderStatus" name="order_status" required>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Uložiť zmeny</button>
                    </form>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php include 'footer.php'; ?>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script noModule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
