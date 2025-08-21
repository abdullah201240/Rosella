<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode('my_orders.php'));
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Ensure orders table exists
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    address2 VARCHAR(255) NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postcode VARCHAR(50) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL,
    order_notes VARCHAR(500) NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    products LONGTEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)");

// Ensure created_at exists even if orders table was created earlier without it; backfill from order_date if present
$checkCreatedAt = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'created_at'");
if ($checkCreatedAt && ($row = $checkCreatedAt->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
    $checkOrderDate = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'order_date'");
    if ($checkOrderDate && ($od = $checkOrderDate->fetch_assoc()) && (int)$od['cnt'] > 0) {
        $conn->query("UPDATE orders SET created_at = order_date WHERE order_date IS NOT NULL");
    }
}

// Ensure user_id exists; backfill by matching order email to users.email
$checkUserId = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'user_id'");
if ($checkUserId && ($row = $checkUserId->fetch_assoc()) && (int)$row['cnt'] === 0) {
    // Add column near the top for readability order
    $conn->query("ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER id");
    // Try to backfill from existing users table if available
    $checkUsersTable = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
    if ($checkUsersTable && ($ut = $checkUsersTable->fetch_assoc()) && (int)$ut['cnt'] > 0) {
        $conn->query("UPDATE orders o JOIN users u ON u.email = o.email SET o.user_id = u.id WHERE o.user_id IS NULL");
    }
}

// Ensure status exists as queries/readers expect it
$checkStatus = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'status'");
if ($checkStatus && ($row = $checkStatus->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
}

$orders = [];
$stmt = $conn->prepare('SELECT id, total_amount, products, status, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

function status_label($s) {
    switch ($s) {
        case 'pending': return 'Pending';
        case 'handover': return 'Handover to Delivery Man';
        case 'on_the_way': return 'On the Way';
        case 'completed': return 'Completed';
        case 'canceled': return 'Canceled';
        default: return ucfirst($s);
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosella - My Orders</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <style>
        .orders-container { max-width: 980px; margin: 30px auto; }
        .progress-steps { display:flex; gap:8px; flex-wrap:wrap; }
        .progress-steps .step { padding:6px 10px; border-radius: 14px; background:#eee; font-size:12px; }
        .progress-steps .active { background:#28a745; color:#fff; }
        .order-card { border:1px solid #eee; padding:16px; margin-bottom:16px; border-radius:8px; }
        .order-products ul{ margin:0; padding-left:18px; }
    </style>
</head>
<body>
    <div id="preloder"><div class="loader"></div></div>

    <?php include 'partials/header.php'; ?>

    <section class="breadcrumb-section set-bg" data-setbg="img/Frame3.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>My Orders</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container orders-container">
            <?php if (count($orders) === 0): ?>
                <div class="alert alert-info">You have not placed any orders yet.</div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php $products = json_decode($order['products'], true) ?: []; ?>
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Order #<?php echo (int)$order['id']; ?></strong>
                                <div>Placed on <?php echo htmlspecialchars($order['created_at']); ?></div>
                            </div>
                            <div><strong>Total ৳<?php echo number_format($order['total_amount'], 2); ?></strong></div>
                        </div>
                        <div class="order-products mt-2">
                            <ul>
                                <?php foreach ($products as $p): ?>
                                    <li><?php echo htmlspecialchars($p['product_name']); ?> — Qty: <?php echo (int)$p['quantity']; ?> — ৳<?php echo number_format($p['product_price'], 2); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="progress-steps mt-2">
                            <?php 
                                $steps = ['pending','handover','on_the_way','completed'];
                                foreach ($steps as $s):
                                    $active = $s === $order['status'] ? 'active' : '';
                            ?>
                                <div class="step <?php echo $active; ?>"><?php echo status_label($s); ?></div>
                            <?php endforeach; ?>
                            <?php if ($order['status'] === 'canceled'): ?>
                                <div class="step active">Canceled</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/mixitup.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>


