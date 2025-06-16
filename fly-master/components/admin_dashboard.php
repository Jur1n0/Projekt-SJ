<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';
require_once '../classes/News.php'; // Zahrnutie triedy News

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

// Určenie aktuálne zobrazenej sekcie
$current_section = $_GET['section'] ?? 'users';
?>
<?php include("head.php") ?>
<?php include 'header.php'; ?>

    <title>Admin Dashboard - Fly</title>
    </head>
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
        <?php endif; ?>

    </div>
</main>

<script>
    // Funkcie pre správu používateľov
    function openEditModal(id, firstName, lastName, email, role) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editFirstName').value = firstName;
        document.getElementById('editLastName').value = lastName;
        document.getElementById('editEmail').value = email;
        document.getElementById('editRole').value = role;
        document.getElementById('editUserModal').classList.add('show');
    }

    function closeEditModal() {
        document.getElementById('editUserModal').classList.remove('show');
    }

    // Funkcie pre správu noviniek
    function openEditNewsModal(id, nadpis, text, obrazok) {
        document.getElementById('editNewsId').value = id;
        document.getElementById('editNewsNadpis').value = nadpis;
        document.getElementById('editNewsText').value = text.replace(/\\n/g, '\n'); // Nahradí \\n za skutočné nové riadky
        document.getElementById('editNewsObrazok').value = obrazok;
        document.getElementById('editNewsModal').classList.add('show');
    }

    function closeEditNewsModal() {
        document.getElementById('editNewsModal').classList.remove('show');
    }

    window.onclick = function(event) {
        const userModal = document.getElementById('editUserModal');
        const newsModal = document.getElementById('editNewsModal');

        if (event.target === userModal) {
            userModal.classList.remove('show');
        }
        if (event.target === newsModal) {
            newsModal.classList.remove('show');
        }
    }
</script>

<?php include 'footer.php'; ?>