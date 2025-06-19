<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';
require_once '../classes/Sale.php';

$user_id = $_SESSION['user_id'];
$first_name = "Neznámy";
$last_name = "Neznáme";
$email = "Neznámy";
$role = "Neznáma";
$user_sales = [];

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user_obj = new User($pdo_conn);
    $sale_obj = new Sale($pdo_conn);

    $user_obj->id = $user_id;

    if ($user_obj->readOne()) {
        $first_name = $user_obj->first_name;
        $last_name = $user_obj->last_name;
        $email = $user_obj->email;
        $role = $user_obj->role;
    } else {
        $_SESSION['message'] = "Chyba: Vaše dáta sa nenašli.";
        $_SESSION['message_type'] = "error";
        header("Location: logout.php");
        exit();
    }

    $stmt_sales = $sale_obj->readByUserId($user_id);
    if ($stmt_sales && $stmt_sales->rowCount() > 0) {
        while ($row = $stmt_sales->fetch(PDO::FETCH_ASSOC)) {
            $user_sales[] = $row;
        }
    }

} catch (Exception $e) {
    $_SESSION['message'] = "Chyba pri načítaní profilu alebo objednávok: " . $e->getMessage();
    $_SESSION['message_type'] = "error";
    error_log("User dashboard data fetch error: " . $e->getMessage());
}
?>
<?php include("head.php") ?>
<?php include("header.php") ?>
<body>
<main id="user-dashboard-page" class="main-content">
    <div class="dashboard-container">
        <h2>Vitajte, <?php echo htmlspecialchars($first_name); ?>!</h2>
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
        <p>Toto je váš osobný účet. Tu môžete spravovať svoje rezervácie a osobné údaje.</p>
        <h3>Vaše údaje:</h3>
        <p><strong>Meno:</strong> <?php echo htmlspecialchars($first_name); ?></p>
        <p><strong>Priezvisko:</strong> <?php echo htmlspecialchars($last_name); ?></p>
        <p><strong>E-mail:</strong> <?php echo htmlspecialchars($email); ?></p>
        <p><strong>Rola:</strong> <?php echo htmlspecialchars($role); ?></p>

        <h3 style="margin-top: 40px;">História vašich objednávok:</h3>
        <?php if (!empty($user_sales)): ?>
            <table>
                <thead>
                <tr>
                    <th>ID Objednávky</th>
                    <th>Let</th>
                    <th>Dátum odletu</th>
                    <th>Cena objednávky</th>
                    <th>Servisný balík</th>
                    <th>Odvoz na letisko</th>
                    <th>Odvoz z letiska</th>
                    <th>Poznámky</th>
                    <th>Metóda platby</th>
                    <th>Stav platby</th>
                    <th>Stav objednávky</th>
                    <th>Dátum objednávky</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($user_sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['id_sale']); ?></td>
                        <td><?php echo htmlspecialchars($sale['lietadlo'] . ' (' . $sale['miesto_odletu'] . ' &rarr; ' . $sale['miesto_priletu'] . ')'); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sale['datum_cas_odletu']))); ?></td>
                        <td>€<?php echo number_format($sale['total_price'], 0, ',', ' '); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($sale['service_package'])); ?></td>
                        <td><?php echo ($sale['pickup_service'] ? 'Áno' : 'Nie'); ?></td>
                        <td><?php echo ($sale['dropoff_service'] ? 'Áno' : 'Nie'); ?></td>
                        <td><?php echo htmlspecialchars($sale['notes'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($sale['payment_method'])); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($sale['payment_status'])); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($sale['order_status'])); ?></td>
                        <td><?php echo htmlspecialchars(date('d.m.Y H:i', strtotime($sale['sale_date']))); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nemáte žiadne minulé objednávky.</p>
        <?php endif; ?>
    </div>
</main>

</body>
<?php include("footer.php") ?>
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</html>