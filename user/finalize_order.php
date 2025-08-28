<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
include '../includes/SSLCommerz.php';
session_start();

// Require login to finalize the pending order
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode('finalize_order.php'));
    exit();
}

// If no pending order, go to checkout
if (!isset($_SESSION['pending_order'])) {
    header('Location: checkout.php');
    exit();
}

$pending = $_SESSION['pending_order'];

// Initialize SSLCommerz
$sslcommerz = new SSLCommerz();

// If this is a payment success callback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tran_id'])) {
    // Validate payment response from SSLCommerz
    $validation = $sslcommerz->validateResponse($_POST);
    
    if ($validation['status'] === 'success') {
        // Process the order after successful payment
        $tran_id = $_POST['tran_id'];
        $amount = $_POST['amount'];
        $currency = $_POST['currency'];
        
        // Get pending order from session
        $order = $_SESSION['pending_order'];
        
        // Insert order into database
        $stmt = $conn->prepare("INSERT INTO orders (
            user_id, first_name, last_name, country, address, address2, 
            city, state, postcode, phone, email, order_notes, 
            total_amount, products, status, payment_method, 
            payment_status, payment_id, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'processing', 
                 'SSLCommerz', 'completed', ?, NOW())");
        
        $products_json = json_encode($order['products']);
        
        $stmt->bind_param(
            "isssssssssssdsss",
            $_SESSION['user_id'],
            $order['first_name'],
            $order['last_name'],
            $order['country'],
            $order['address'],
            $order['address2'],
            $order['city'],
            $order['state'],
            $order['postcode'],
            $order['phone'],
            $order['email'],
            $order['order_notes'],
            $order['total_amount'],
            $products_json,
            $tran_id
        );
        
        if ($stmt->execute()) {
            $order_id = $conn->insert_id;
            
            // Clear the cart
            $session_id = session_id();
            $conn->query("DELETE FROM carts WHERE session_id = '$session_id'");
            
            // Clear pending order from session
            unset($_SESSION['pending_order']);
            
            // Redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit();
        } else {
            $error = "Error processing order: " . $conn->error;
        }
    } else {
        $error = "Payment validation failed: " . $validation['message'];
    }
}

// If we get here, process payment
$tran_id = 'TXN' . time();
$post_data = [
    'total_amount' => $pending['total_amount'],
    'tran_id' => $tran_id,
    'customer_name' => $pending['first_name'] . ' ' . $pending['last_name'],
    'customer_email' => $pending['email'],
    'customer_address' => $pending['address'],
    'customer_address2' => $pending['address2'] ?? '',
    'customer_city' => $pending['city'],
    'customer_state' => $pending['state'],
    'customer_postcode' => $pending['postcode'],
    'customer_country' => $pending['country'],
    'customer_phone' => $pending['phone'],
    'success_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/finalize_order.php',
    'fail_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment_failed.php',
    'cancel_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/checkout.php',
    'ipn_url' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/payment_ipn.php',
    'product_name' => 'Order #' . $tran_id,
    'product_profile' => 'physical-goods'
];

// Create SSLCommerz session
$result = $sslcommerz->createSession($post_data);
$payment_url = $result['GatewayPageURL'] ?? '';
if ($payment_url) {
    header("Location: $payment_url");
    exit();
} else {
    $error = "Failed to initiate payment. Please try again.";
}

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
