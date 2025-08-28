<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection and SSLCommerz class
include '../db.php';
include '../includes/SSLCommerz.php';

// Start the session
session_start();

// Ensure all required columns exist in the orders table
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
    payment_transaction_id VARCHAR(100) NULL,
    payment_status VARCHAR(20) DEFAULT 'pending',
    payment_error TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
)");

// Ensure payment_transaction_id column exists
$checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_transaction_id'");
if ($checkCol && ($row = $checkCol->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_transaction_id VARCHAR(100) NULL AFTER status");
}

// Ensure payment_status column exists
$checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_status'");
if ($checkCol && ($row = $checkCol->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending' AFTER payment_transaction_id");
}

// Ensure payment_error column exists
$checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_error'");
if ($checkCol && ($row = $checkCol->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_error TEXT NULL AFTER payment_status");
}

// Ensure updated_at column exists
$checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'updated_at'");
if ($checkCol && ($row = $checkCol->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
}

// Check if order ID is provided and matches the session
if (!isset($_GET['order_id']) || empty($_GET['order_id'])) {
    die("Error: No order ID provided");
}

$order_id = (int)$_GET['order_id'];

// Verify the order ID matches the session to prevent unauthorized access
if (!isset($_SESSION['pending_order_id']) || $_SESSION['pending_order_id'] != $order_id) {
    die("Error: Invalid order ID");
}

// Fetch order details
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: checkout.php");
    exit();
}

$order = $result->fetch_assoc();
$stmt->close();

// Initialize SSLCommerz
$sslcommerz = new SSLCommerz();

// Prepare order data for SSLCommerz
$order_data = [
    'total_amount' => $order['total_amount'],
    'tran_id' => $sslcommerz->generateTranId(),
    'customer_name' => $order['first_name'] . ' ' . $order['last_name'],
    'customer_email' => $order['email'],
    'customer_address' => $order['address'],
    'customer_address2' => $order['address2'] ?? '',
    'customer_city' => $order['city'],
    'customer_state' => $order['state'],
    'customer_postcode' => $order['postcode'],
    'customer_country' => $order['country'],
    'customer_phone' => $order['phone'],
    'order_id' => $order_id,
    'user_id' => $order['user_id'] ?? '',
    'session_id' => $_SESSION['session_id'] ?? ''
];

// Create SSLCommerz session
$sslcommerz_response = $sslcommerz->createSession($order_data);

if ($sslcommerz_response && isset($sslcommerz_response['GatewayPageURL'])) {
    // Update order with transaction ID and payment status
    $sql = "UPDATE orders SET 
            payment_transaction_id = ?, 
            status = 'processing',
            payment_status = 'processing',
            status = 'payment_pending'
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $order_data['tran_id'], $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Store session key in session for later verification
    $_SESSION['sslcommerz_session_key'] = $sslcommerz_response['sessionkey'];
    $_SESSION['current_order_id'] = $order_id;
    
    // Store the order ID in session for verification after payment
    $_SESSION['processing_order_id'] = $order_id;
    
    // Redirect to SSLCommerz payment page
    header("Location: " . $sslcommerz_response['GatewayPageURL']);
    exit();
} else {
    // Update order status to failed
    $error_msg = isset($sslcommerz_response['failedreason']) ? $sslcommerz_response['failedreason'] : 'Unknown error';
    $conn->query("UPDATE orders SET status = 'failed', payment_status = 'failed', payment_error = '" . $conn->real_escape_string($error_msg) . "' WHERE id = $order_id");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $error_message, $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to error page
    header("Location: payment_error.php?order_id=" . $order_id . "&error=" . urlencode($error_message));
    exit();
}
?>
