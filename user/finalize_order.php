<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
session_start();

// Require login to finalize the pending order
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode('finalize_order.php'));
    exit();
}

// If no pending order, go to My Orders
if (!isset($_SESSION['pending_order'])) {
    header('Location: my_orders.php');
    exit();
}

$pending = $_SESSION['pending_order'];

// Ensure orders table has status (idempotent safety)
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

$user_id = (int)$_SESSION['user_id'];

// Insert order using pending data
$sql = "INSERT INTO orders (user_id, first_name, last_name, country, address, address2, city, state, postcode, phone, email, order_notes, total_amount, products)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('Error preparing statement: ' . $conn->error);
}

$stmt->bind_param(
    'isssssssssssds',
    $user_id,
    $pending['first_name'],
    $pending['last_name'],
    $pending['country'],
    $pending['address'],
    $pending['address2'],
    $pending['city'],
    $pending['state'],
    $pending['postcode'],
    $pending['phone'],
    $pending['email'],
    $pending['order_notes'],
    $pending['total_amount'],
    $pending['products_json']
);

if (!$stmt->execute()) {
    die('Error: ' . $stmt->error);
}

// Clear pending order
unset($_SESSION['pending_order']);

// Clear the cart by session id if exists
if (isset($_SESSION['session_id'])) {
    $session_id = $_SESSION['session_id'];
    $sql_clear_cart = 'DELETE FROM carts WHERE session_id = ?';
    $stmt_clear_cart = $conn->prepare($sql_clear_cart);
    if ($stmt_clear_cart) {
        $stmt_clear_cart->bind_param('s', $session_id);
        $stmt_clear_cart->execute();
        $stmt_clear_cart->close();
    }
}

// Upsert user profile details from the order
$conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    address VARCHAR(255) NULL,
    address2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postcode VARCHAR(50) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$sql_upsert = "INSERT INTO user_profiles (user_id, first_name, last_name, country, address, address2, city, state, postcode, phone, email)
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
               ON DUPLICATE KEY UPDATE first_name=VALUES(first_name), last_name=VALUES(last_name), country=VALUES(country), address=VALUES(address), address2=VALUES(address2), city=VALUES(city), state=VALUES(state), postcode=VALUES(postcode), phone=VALUES(phone), email=VALUES(email)";
$stmt_up = $conn->prepare($sql_upsert);
if ($stmt_up) {
    $stmt_up->bind_param(
        'issssssssss',
        $user_id,
        $pending['first_name'],
        $pending['last_name'],
        $pending['country'],
        $pending['address'],
        $pending['address2'],
        $pending['city'],
        $pending['state'],
        $pending['postcode'],
        $pending['phone'],
        $pending['email']
    );
    $stmt_up->execute();
    $stmt_up->close();
}

// Done
header('Location: order_success.php');
exit();
?>
