<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Start the session
session_start();

// Ensure users and orders tables exist and orders has status
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

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

// Ensure status column exists if table was created earlier without it
$checkCol = $conn->query("SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'status'");
if ($checkCol && ($row = $checkCol->fetch_assoc()) && (int)$row['cnt'] === 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending'");
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $country = htmlspecialchars($_POST['country']);
    $address = htmlspecialchars($_POST['address']);
    $address2 = htmlspecialchars($_POST['address2']);
    $city = htmlspecialchars($_POST['city']);
    $state = htmlspecialchars($_POST['state']);
    $postcode = htmlspecialchars($_POST['postcode']);
    $phone = htmlspecialchars($_POST['phone']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $order_notes = htmlspecialchars($_POST['order_notes']);
    $total_amount = floatval($_POST['total_amount']);
    $products = json_decode($_POST['products'], true); // Decode JSON string to array

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($country) || empty($address) || empty($city) || empty($state) || empty($postcode) || empty($phone) || empty($email)) {
        die("Error: All required fields must be filled.");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email address.");
    }

    // Validate products data
    if (!is_array($products) || empty($products)) {
        die("Error: No products in the cart.");
    }

    // Convert products array back to JSON for database storage
    $products_json = json_encode($products);

    // Determine user: logged in, existing email, or create account
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

    if (!$user_id) {
        // Check if email already exists
        $stmtUser = $conn->prepare('SELECT id, name FROM users WHERE email = ? LIMIT 1');
        $stmtUser->bind_param('s', $email);
        $stmtUser->execute();
        $resUser = $stmtUser->get_result();
        if ($existing = $resUser->fetch_assoc()) {
            // Save pending order in session and redirect to login to ask password
            $_SESSION['pending_order'] = [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'country' => $country,
                'address' => $address,
                'address2' => $address2,
                'city' => $city,
                'state' => $state,
                'postcode' => $postcode,
                'phone' => $phone,
                'email' => $email,
                'order_notes' => $order_notes,
                'total_amount' => $total_amount,
                'products_json' => $products_json
            ];
            $stmtUser->close();
            header('Location: login.php?redirect=' . urlencode('finalize_order.php') . '&email=' . urlencode($email) . '&notice=' . urlencode('We found an existing account. Please enter your password to place the order.'));
            exit();
        }
        $stmtUser->close();

        // Create account automatically for new email and auto-login
        $autoName = trim(($first_name . ' ' . $last_name));
        $generatedPassword = bin2hex(random_bytes(4)); // 8 hex chars
        $hashed = password_hash($generatedPassword, PASSWORD_BCRYPT);
        $stmtInsertUser = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $stmtInsertUser->bind_param('sss', $autoName, $email, $hashed);
        if ($stmtInsertUser->execute()) {
            $_SESSION['user_id'] = $stmtInsertUser->insert_id;
            $_SESSION['user_name'] = $autoName;
            $_SESSION['account_created'] = [
                'email' => $email,
                'password' => $generatedPassword
            ];
            $user_id = (int)$_SESSION['user_id'];
        } else {
            die('Error creating user account.');
        }
        $stmtInsertUser->close();
    }

    // Insert order into the database (now with user_id assured if account created)
    $sql = "INSERT INTO orders (user_id, first_name, last_name, country, address, address2, city, state, postcode, phone, email, order_notes, total_amount, products)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssssds",
        $user_id,
        $first_name,
        $last_name,
        $country,
        $address,
        $address2,
        $city,
        $state,
        $postcode,
        $phone,
        $email,
        $order_notes,
        $total_amount,
        $products_json
    );

    if ($stmt->execute()) {
        // Clear the cart (if applicable)
        $session_id = $_SESSION['session_id'];
        $sql_clear_cart = "DELETE FROM carts WHERE session_id = ?";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("s", $session_id);
        $stmt_clear_cart->execute();
        $stmt_clear_cart->close();

        // Save or update profile from the submitted checkout details
        if ($user_id) {
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
            $stmt_up->bind_param("issssssssss", $user_id, $first_name, $last_name, $country, $address, $address2, $city, $state, $postcode, $phone, $email);
            $stmt_up->execute();
            $stmt_up->close();
        }

        $stmt->close();
        header("Location: order_success.php");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }

    // No further processing here
}

// Redirect if the form is not submitted
header("Location: checkout.php");
exit();
?>