<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Start the session
session_start();

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

    // Insert order into the database
    $sql = "INSERT INTO orders (first_name, last_name, country, address, address2, city, state, postcode, phone, email, order_notes, total_amount, products)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "ssssssssssdss",
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

    // Execute the statement
    if ($stmt->execute()) {
        // Clear the cart (if applicable)
        $session_id = $_SESSION['session_id'];
        $sql_clear_cart = "DELETE FROM carts WHERE session_id = ?";
        $stmt_clear_cart = $conn->prepare($sql_clear_cart);
        $stmt_clear_cart->bind_param("s", $session_id);
        $stmt_clear_cart->execute();
        $stmt_clear_cart->close();

        // Redirect to a success page
        header("Location: order_success.php");
        exit();
    } else {
        // Handle database error
        die("Error: " . $stmt->error);
    }

    // Close the statement
    $stmt->close();
}

// Redirect if the form is not submitted
header("Location: checkout.php");
exit();
?>