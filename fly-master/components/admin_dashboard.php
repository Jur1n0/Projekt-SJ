<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';
require_once '../classes/News.php'; // Zahrnutie triedy News
require_once '../classes/Flight.php'; // NOVÉ
require_once '../classes/Sale.php';   // NOVÉ

$users = [];
$all_news = [];
$all_flights = []; // Pre admin dashboard
$all_sales = []; // Pre admin dashboard

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user_obj = new User($pdo_conn);
    $news_obj = new News($pdo_conn);
    $flight_obj = new Flight($pdo_conn);
    $sale_obj = new Sale($pdo_conn);

    // Načítanie používateľov
    $stmt_users = $user_obj->readAll();
    if ($stmt_users && $stmt_users->rowCount() > 0) {
        while($row = $stmt_users->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
    }

    // Načítanie noviniek
    $stmt_news = $news_obj->readAll();
    if ($stmt_news && $stmt_news->rowCount() > 0) {
        while ($row = $stmt_news->fetch(PDO::FETCH_ASSOC)) {
            $all_news[] = $row;
        }
    }

    // Načítanie letov (pre správu letov v admin paneli)
    $stmt_flights = $flight_obj->readFilteredAndSorted(); // Získanie všetkých letov bez filtra
    if ($stmt_flights && $stmt_flights->rowCount() > 0) {
        while($row = $stmt_flights->fetch(PDO::FETCH_ASSOC)) {
            $all_flights[] = $row;
        }
    }

    // Načítanie objednávok (pre správu objednávok v admin paneli)
    $stmt_sales = $sale_obj->readAll();
    if ($stmt_sales && $stmt_sales->rowCount() > 0) {
        while($row = $stmt_sales->fetch(PDO::FETCH_ASSOC)) {
            $all_sales[] = $row;
        }
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní dát pre dashboard: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("Admin dashboard data fetch error: " . $e->getMessage());
}

$current_section = $_GET['section'] ?? 'users'; // Predvolená sekcia

?>
<?php include("head.php") ?>
<body>
<main id="admin-dashboard-page" class="main-content">
    <div class="dashboard-container">
        <h2>Admin Dashboard</h2>
        <a href="logout.php" class="btn-logout">Odhlásiť sa</a>
        <?php
        if (isset($_SESSION['message'])) {
            $class = strpos($_SESSION['message_type'], 'success') !== false ? 'success-message' : 'error-message';
            if ($class === 'error-message' && strpos($_SESSION['message_type'], 'info') !== false) {
                $class = 'info-message';
            } elseif ($class === 'error-message' && strpos($_SESSION['message_type'], 'warning') !== false) {
                $class = 'warning-message';
            }
            echo "<p class='message {$class}'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>
        <div class="dashboard-navigation">
            <a href="?section=users" class="nav-button <?php echo ($current_section === 'users' ? 'active' : ''); ?>">Správa Používateľov</a>
            <a href="?section=flights" class="nav-button <?php echo ($current_section === 'flights' ? 'active' : ''); ?>">Správa Letov</a>
            <a href="?section=news" class="nav-button <?php echo ($current_section === 'news' ? 'active' : ''); ?>">Správa Noviniek</a>
            <a href="?section=orders" class="nav-button <?php echo ($current_section === 'orders' ? 'active' : ''); ?>">Správa Objednávok</a>
        </div>

        <div id="users-section" class="dashboard-section <?php echo ($current_section === 'users' ? 'active' : ''); ?>">
            <h3>Správa Používateľov</h3>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Meno</th>
                    <th>Priezvisko</th>
                    <th>Email</th>
                    <th>Rola</th>
                    <th>GDPR súhlas</th>
                    <th>Vytvorené</th>
                    <th>Aktualizované</th>
                    <th>Akcie</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo ($user['gdpr_consent'] ? 'Áno' : 'Nie'); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($user['updated_at'] ?? 'N/A'); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-edit" onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">Upraviť</button>
                                <form action="../process/process_delete_user.php" method="POST" onsubmit="return confirm('Naozaj chcete vymazať tohto používateľa?');" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <button type="submit" class="btn btn-delete">Vymazať</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Žiadni používatelia na zobrazenie.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="flights-section" class="dashboard-section <?php echo ($current_section === 'flights' ? 'active' : ''); ?>">
            <h3>Správa Letov</h3>
            <button class="btn btn-add" onclick="openAddFlightModal()">Pridať Nový Let</button>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Lietadlo</th>
                    <th>Odlet</th>
                    <th>Prílet</th>
                    <th>Dátum/Čas Odletu</th>
                    <th>Dátum/Čas Príletu</th>
                    <th>Cena</th>
                    <th>Kapacita</th>
                    <th>Dĺžka Letu (hod/min)</th>
                    <th>Obrázok</th>
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
                            <td><?php echo htmlspecialchars($flight['datum_cas_odletu']); ?></td>
                            <td><?php echo htmlspecialchars($flight['datum_cas_priletu']); ?></td>
                            <td>€<?php echo number_format($flight['cena'], 0, ',', ' '); ?></td>
                            <td><?php echo htmlspecialchars($flight['kapacita_lietadla']); ?></td>
                            <td><?php echo htmlspecialchars($flight['dlzka_letu_hodiny']); ?>h <?php echo htmlspecialchars($flight['dlzka_letu_minuty']); ?>m</td>
                            <td><?php echo !empty($flight['obrazok']) ? htmlspecialchars($flight['obrazok']) : 'N/A'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-edit" onclick="openEditFlightModal(<?php echo htmlspecialchars(json_encode($flight)); ?>)">Upraviť</button>
                                <form action="../process/process_delete_flight.php" method="POST" onsubmit="return confirm('Naozaj chcete vymazať tento let?');" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($flight['id']); ?>">
                                    <button type="submit" class="btn btn-delete">Vymazať</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">Žiadne lety na zobrazenie.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="news-section" class="dashboard-section <?php echo ($current_section === 'news' ? 'active' : ''); ?>">
            <h3>Správa Noviniek</h3>
            <button class="btn btn-add" onclick="openAddNewsModal()">Pridať Novú Novinku</button>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nadpis</th>
                    <th>Obsah</th>
                    <th>Autor</th>
                    <th>Dátum</th>
                    <th>Aktualizované</th>
                    <th>Akcie</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($all_news)): ?>
                    <?php foreach ($all_news as $news_item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($news_item['id']); ?></td>
                            <td><?php echo htmlspecialchars($news_item['title']); ?></td>
                            <td><?php echo htmlspecialchars(substr($news_item['content'], 0, 100)); ?>...</td>
                            <td><?php echo htmlspecialchars($news_item['author']); ?></td>
                            <td><?php echo htmlspecialchars($news_item['publish_date']); ?></td>
                            <td><?php echo htmlspecialchars($news_item['updated_at'] ?? 'N/A'); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-edit" onclick="openEditNewsModal(<?php echo htmlspecialchars(json_encode($news_item)); ?>)">Upraviť</button>
                                <form action="../process/process_delete_news.php" method="POST" onsubmit="return confirm('Naozaj chcete vymazať túto novinku?');" style="display:inline-block;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($news_item['id']); ?>">
                                    <button type="submit" class="btn btn-delete">Vymazať</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Žiadne novinky na zobrazenie.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="orders-section" class="dashboard-section <?php echo ($current_section === 'orders' ? 'active' : ''); ?>">
            <h3>Správa Objednávok</h3>
            <table>
                <thead>
                <tr>
                    <th>ID Objednávky</th>
                    <th>Používateľ</th>
                    <th>Email Používateľa</th>
                    <th>Let</th>
                    <th>Cena</th>
                    <th>Balík</th>
                    <th>Odvoz na</th>
                    <th>Odvoz z</th>
                    <th>Poznámky</th>
                    <th>Metóda platby</th>
                    <th>Status platby</th>
                    <th>Status objednávky</th>
                    <th>Dátum objednávky</th>
                    <th>Akcie</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($all_sales)): ?>
                    <?php foreach ($all_sales as $sale): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sale['id_sale']); ?></td>
                            <td><?php echo htmlspecialchars($sale['first_name'] . ' ' . $sale['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['email']); ?></td>
                            <td><?php echo htmlspecialchars($sale['lietadlo'] . ' (' . $sale['miesto_odletu'] . ' &rarr; ' . $sale['miesto_priletu'] . ')'); ?></td>
                            <td>€<?php echo number_format($sale['total_price'], 0, ',', ' '); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($sale['service_package'])); ?></td>
                            <td><?php echo ($sale['pickup_service'] ? 'Áno' : 'Nie'); ?></td>
                            <td><?php echo ($sale['dropoff_service'] ? 'Áno' : 'Nie'); ?></td>
                            <td><?php echo htmlspecialchars($sale['notes'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($sale['payment_method'])); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($sale['payment_status'])); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($sale['order_status'])); ?></td>
                            <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sale['sale_date']))); ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-edit" onclick="openEditSaleModal(<?php echo htmlspecialchars($sale['id_sale']); ?>, '<?php echo htmlspecialchars($sale['payment_status']); ?>', '<?php echo htmlspecialchars($sale['order_status']); ?>')">Upraviť Status</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="14">Žiadne objednávky na zobrazenie.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<?php include("footer.php") ?>

<div id="addFlightModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeAddFlightModal()">&times;</span>
        <h2>Pridať Nový Let</h2>
        <form action="../process/process_add_flight.php" method="POST">
            <div class="form-group">
                <label for="addLietadlo">Názov lietadla:</label>
                <input type="text" id="addLietadlo" name="lietadlo" required>
            </div>
            <div class="form-group">
                <label for="addMiestoOdletu">Miesto odletu:</label>
                <input type="text" id="addMiestoOdletu" name="miesto_odletu" required>
            </div>
            <div class="form-group">
                <label for="addMiestoPriletu">Miesto príletu:</label>
                <input type="text" id="addMiestoPriletu" name="miesto_priletu" required>
            </div>
            <div class="form-group">
                <label for="addDatumCasOdletu">Dátum a čas odletu:</label>
                <input type="datetime-local" id="addDatumCasOdletu" name="datum_cas_odletu" required>
            </div>
            <div class="form-group">
                <label for="addDatumCasPriletu">Dátum a čas príletu:</label>
                <input type="datetime-local" id="addDatumCasPriletu" name="datum_cas_priletu" required>
            </div>
            <div class="form-group">
                <label for="addCena">Cena za lietadlo (€):</label>
                <input type="number" id="addCena" name="cena" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="addKapacitaLietadla">Kapacita lietadla (počet osôb):</label>
                <input type="number" id="addKapacitaLietadla" name="kapacita_lietadla" min="1" required>
            </div>
            <div class="form-group">
                <label for="addDlzkaLetuHodiny">Dĺžka letu (hodiny):</label>
                <input type="number" id="addDlzkaLetuHodiny" name="dlzka_letu_hodiny" min="0" required>
            </div>
            <div class="form-group">
                <label for="addDlzkaLetuMinuty">Dĺžka letu (minúty):</label>
                <input type="number" id="addDlzkaLetuMinuty" name="dlzka_letu_minuty" min="0" max="59" required>
            </div>
            <div class="form-group">
                <label for="addObrazok">Názov súboru obrázku (napr. image.png):</label>
                <input type="text" id="addObrazok" name="obrazok" placeholder="voliteľné">
            </div>
            <button type="submit" class="btn btn-primary">Pridať Let</button>
            <button type="button" class="btn btn-secondary" onclick="closeAddFlightModal()">Zrušiť</button>
        </form>
    </div>
</div>

<div id="editFlightModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditFlightModal()">&times;</span>
        <h2>Upraviť Let</h2>
        <form action="../process/process_update_flight.php" method="POST">
            <input type="hidden" id="editFlightId" name="id">
            <div class="form-group">
                <label for="editLietadlo">Názov lietadla:</label>
                <input type="text" id="editLietadlo" name="lietadlo" required>
            </div>
            <div class="form-group">
                <label for="editMiestoOdletu">Miesto odletu:</label>
                <input type="text" id="editMiestoOdletu" name="miesto_odletu" required>
            </div>
            <div class="form-group">
                <label for="editMiestoPriletu">Miesto príletu:</label>
                <input type="text" id="editMiestoPriletu" name="miesto_priletu" required>
            </div>
            <div class="form-group">
                <label for="editDatumCasOdletu">Dátum a čas odletu:</label>
                <input type="datetime-local" id="editDatumCasOdletu" name="datum_cas_odletu" required>
            </div>
            <div class="form-group">
                <label for="editDatumCasPriletu">Dátum a čas príletu:</label>
                <input type="datetime-local" id="editDatumCasPriletu" name="datum_cas_priletu" required>
            </div>
            <div class="form-group">
                <label for="editCena">Cena za lietadlo (€):</label>
                <input type="number" id="editCena" name="cena" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="editKapacitaLietadla">Kapacita lietadla (počet osôb):</label>
                <input type="number" id="editKapacitaLietadla" name="kapacita_lietadla" min="1" required>
            </div>
            <div class="form-group">
                <label for="editDlzkaLetuHodiny">Dĺžka letu (hodiny):</label>
                <input type="number" id="editDlzkaLetuHodiny" name="dlzka_letu_hodiny" min="0" required>
            </div>
            <div class="form-group">
                <label for="editDlzkaLetuMinuty">Dĺžka letu (minúty):</label>
                <input type="number" id="editDlzkaLetuMinuty" name="dlzka_letu_minuty" min="0" max="59" required>
            </div>
            <div class="form-group">
                <label for="editObrazok">Názov súboru obrázku (napr. image.png):</label>
                <input type="text" id="editObrazok" name="obrazok" placeholder="voliteľné">
            </div>
            <button type="submit" class="btn btn-primary">Uložiť Zmeny</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditFlightModal()">Zrušiť</button>
        </form>
    </div>
</div>


<div id="editUserModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditUserModal()">&times;</span>
        <h2>Upraviť Používateľa</h2>
        <form action="../process/process_update_user.php" method="POST">
            <input type="hidden" id="editUserId" name="id">
            <div class="form-group">
                <label for="editFirstName">Meno:</label>
                <input type="text" id="editFirstName" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="editLastName">Priezvisko:</label>
                <input type="text" id="editLastName" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="editEmail">Email:</label>
                <input type="email" id="editEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="editRole">Rola:</label>
                <select id="editRole" name="role" class="form-select">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" id="editGdprConsent" name="gdpr_consent" value="1">
                <label for="editGdprConsent">Súhlasím s GDPR</label>
            </div>
            <button type="submit" class="btn btn-primary">Uložiť Zmeny</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Zrušiť</button>
        </form>
    </div>
</div>

<div id="addNewsModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeAddNewsModal()">&times;</span>
        <h2>Pridať Novú Novinku</h2>
        <form action="../process/process_add_news.php" method="POST">
            <div class="form-group">
                <label for="addNewsTitle">Nadpis:</label>
                <input type="text" id="addNewsTitle" name="title" required>
            </div>
            <div class="form-group">
                <label for="addNewsContent">Obsah:</label>
                <textarea id="addNewsContent" name="content" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="addNewsAuthor">Autor:</label>
                <input type="text" id="addNewsAuthor" name="author" required>
            </div>
            <button type="submit" class="btn btn-primary">Pridať Novinku</button>
            <button type="button" class="btn btn-secondary" onclick="closeAddNewsModal()">Zrušiť</button>
        </form>
    </div>
</div>

<div id="editNewsModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditNewsModal()">&times;</span>
        <h2>Upraviť Novinku</h2>
        <form action="../process/process_update_news.php" method="POST">
            <input type="hidden" id="editNewsId" name="id">
            <div class="form-group">
                <label for="editNewsTitle">Nadpis:</label>
                <input type="text" id="editNewsTitle" name="title" required>
            </div>
            <div class="form-group">
                <label for="editNewsContent">Obsah:</label>
                <textarea id="editNewsContent" name="content" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="editNewsAuthor">Autor:</label>
                <input type="text" id="editNewsAuthor" name="author" required>
            </div>
            <button type="submit" class="btn btn-primary">Uložiť Zmeny</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditNewsModal()">Zrušiť</button>
        </form>
    </div>
</div>

<div id="editSaleModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditSaleModal()">&times;</span>
        <h2>Upraviť Status Objednávky</h2>
        <form action="../process/process_update_sale.php" method="POST">
            <input type="hidden" id="editSaleId" name="id_sale">
            <div class="form-group">
                <label for="editSalePaymentStatus">Stav platby:</label>
                <select id="editSalePaymentStatus" name="payment_status" class="form-select">
                    <option value="pending">Čaká na platbu</option>
                    <option value="paid">Zaplatené</option>
                </select>
            </div>
            <div class="form-group">
                <label for="editSaleOrderStatus">Stav objednávky:</label>
                <select id="editSaleOrderStatus" name="order_status" class="form-select">
                    <option value="pending">Čaká</option>
                    <option value="cancelled">Zrušené</option>
                    <option value="completed">Dokončené</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Uložiť Zmeny</button>
            <button type="button" class="btn btn-secondary" onclick="closeEditSaleModal()">Zrušiť</button>
        </form>
    </div>
</div>


<script>
    // Funkcie pre prepínanie sekcií
    function showSection(sectionId) {
        document.querySelectorAll('.dashboard-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');

        document.querySelectorAll('.nav-button').forEach(button => {
            button.classList.remove('active');
        });
        document.querySelector(`.nav-button[href="?section=${sectionId.replace('-section', '')}"]`).classList.add('active');
    }

    // Načítanie sekcie z URL pri načítaní stránky
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const section = urlParams.get('section') || 'users';
        showSection(section + '-section');
    });

    // Modálne okná - funkcie sú v JS súbore alebo tu priamo

    // Používateľské modálne okno
    function openEditUserModal(user) {
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editFirstName').value = user.first_name;
        document.getElementById('editLastName').value = user.last_name;
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editGdprConsent').checked = user.gdpr_consent == 1;
        document.getElementById('editUserModal').classList.add('show');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModal').classList.remove('show');
    }

    // Novinky modálne okno
    function openAddNewsModal() {
        document.getElementById('addNewsModal').classList.add('show');
    }

    function closeAddNewsModal() {
        document.getElementById('addNewsModal').classList.remove('show');
    }

    function openEditNewsModal(newsItem) {
        document.getElementById('editNewsId').value = newsItem.id;
        document.getElementById('editNewsTitle').value = newsItem.title;
        document.getElementById('editNewsContent').value = newsItem.content;
        document.getElementById('editNewsAuthor').value = newsItem.author;
        document.getElementById('editNewsModal').classList.add('show');
    }

    function closeEditNewsModal() {
        document.getElementById('editNewsModal').classList.remove('show');
    }

    // Lety modálne okná
    function openAddFlightModal() {
        document.getElementById('addFlightModal').classList.add('show');
    }

    function closeAddFlightModal() {
        document.getElementById('addFlightModal').classList.remove('show');
    }

    function openEditFlightModal(flight) {
        document.getElementById('editFlightId').value = flight.id;
        document.getElementById('editLietadlo').value = flight.lietadlo;
        document.getElementById('editMiestoOdletu').value = flight.miesto_odletu;
        document.getElementById('editMiestoPriletu').value = flight.miesto_priletu;
        // Pre formát datetime-local je potrebné správne naformátovať dátum
        document.getElementById('editDatumCasOdletu').value = formatDateTimeLocal(flight.datum_cas_odletu);
        document.getElementById('editDatumCasPriletu').value = formatDateTimeLocal(flight.datum_cas_priletu);
        document.getElementById('editCena').value = flight.cena;
        document.getElementById('editKapacitaLietadla').value = flight.kapacita_lietadla;
        document.getElementById('editDlzkaLetuHodiny').value = flight.dlzka_letu_hodiny;
        document.getElementById('editDlzkaLetuMinuty').value = flight.dlzka_letu_minuty;
        document.getElementById('editObrazok').value = flight.obrazok || '';
        document.getElementById('editFlightModal').classList.add('show');
    }

    function closeEditFlightModal() {
        document.getElementById('editFlightModal').classList.remove('show');
    }

    // Pomocná funkcia pre formátovanie dátumu a času pre input type="datetime-local"
    function formatDateTimeLocal(dateTimeString) {
        const date = new Date(dateTimeString);
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `<span class="math-inline">\{year\}\-</span>{month}-<span class="math-inline">\{day\}T</span>{hours}:${minutes}`;
    }

    // Objednávky modálne okno
    function openEditSaleModal(id, paymentStatus, orderStatus) {
        document.getElementById('editSaleId').value = id;
        document.getElementById('editSalePaymentStatus').value = paymentStatus;
        document.getElementById('editSaleOrderStatus').value = orderStatus;
        document.getElementById('editSaleModal').classList.add('show');
    }

    function closeEditSaleModal() {
        document.getElementById('editSaleModal').classList.remove('show');
    }


    // Zatvorenie modálneho okna kliknutím mimo neho
    window.onclick = function(event) {
        const userModal = document.getElementById('editUserModal');
        const newsAddModal = document.getElementById('addNewsModal');
        const newsEditModal = document.getElementById('editNewsModal');
        const flightAddModal = document.getElementById('addFlightModal');
        const flightEditModal = document.getElementById('editFlightModal');
        const saleEditModal = document.getElementById('editSaleModal');

        if (event.target === userModal) {
            userModal.classList.remove('show');
        }
        if (event.target === newsAddModal) {
            newsAddModal.classList.remove('show');
        }
        if (event.target === newsEditModal) {
            newsEditModal.classList.remove('show');
        }
        if (event.target === flightAddModal) {
            flightAddModal.classList.remove('show');
        }
        if (event.target === flightEditModal) {
            flightEditModal.classList.remove('show');
        }
        if (event.target === saleEditModal) {
            saleEditModal.classList.remove('show');
        }
    }
</script>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>