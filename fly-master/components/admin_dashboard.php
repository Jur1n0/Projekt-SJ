<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';

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
?>
<?php include("head.php") ?>
<?php include 'header.php'; ?>

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

        <h3>Správa užívateľov</h3>
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
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="5">Žiadni používatelia v databáze.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td class="action-buttons">
                            <button class="btn btn-edit" onclick="openEditModal(
                            <?php echo $user['id']; ?>,
                                    '<?php echo htmlspecialchars($user['first_name']); ?>',
                                    '<?php echo htmlspecialchars($user['last_name']); ?>',
                                    '<?php echo htmlspecialchars($user['email']); ?>',
                                    '<?php echo htmlspecialchars($user['role']); ?>'
                                    )">Upraviť</button>
                            <form action="../process/process_delete_user.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Naozaj chcete vymazať tohto užívateľa?');">Vymazať</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <div id="editUserModal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeEditModal()">&times;</span>
                <h2>Upraviť užívateľa</h2>
                <form id="editUserForm" class="modal-form" action="../process/process_update_user.php" method="POST">
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
    </div>
</main>

<script>
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

    window.onclick = function(event) {
        const modal = document.getElementById('editUserModal');
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }
</script>

<?php include 'footer.php'; ?>