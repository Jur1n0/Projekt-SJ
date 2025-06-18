<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header("Location: login.php");
    exit();
}

require_once '../module/Database.php';
require_once '../classes/User.php';
require_once '../classes/Sale.php'; // Zahrnutie triedy Sale

$user_id = $_SESSION['user_id'];
$first_name = "Neznámy";
$last_name = "Neznáme";
$email = "Neznámy";
$role = "Neznáma";
$user_sales = []; // Pole pre objednávky používateľa

try {
    $db = new Database();
    $pdo_conn = $db->getConnection();
    $user_obj = new User($pdo_conn);
    $sale_obj = new Sale($pdo_conn); // Inštancia triedy Sale

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

    // Načítanie objednávok pre prihláseného používateľa
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
<style>
    /* Styling for the user dashboard table, similar to admin dashboard */
    .dashboard-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
        background-color: var(--background-primary);
        border-radius: 10px;
        box-shadow: var(--shadow-2);
        margin-top: 30px;
        margin-bottom: 30px;
    }

    .dashboard-container h2, .dashboard-container h3 {
        color: var(--heading-color);
        text-align: center;
        margin-bottom: 20px;
    }

    .dashboard-container p {
        color: var(--text-color);
        margin-bottom: 10px;
    }

    .dashboard-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .dashboard-container th,
    .dashboard-container td {
        border: 1px solid var(--border-color);
        padding: 10px;
        text-align: left;
        color: var(--text-color);
    }

    .dashboard-container th {
        background-color: var(--background-secondary);
        font-weight: bold;
        color: var(--heading-color);
    }

    .dashboard-container tr:nth-child(even) {
        background-color: var(--background-light);
    }

    .dashboard-container tr:hover {
        background-color: var(--background-hover);
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

<?php include("footer.php") ?>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>
</html>