<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Get the cart ID and quantity from the POST request
$cart_id = intval($_POST['cart_id']);
$quantity = intval($_POST['quantity']);

// Update the quantity in the database
$sql = "UPDATE carts SET quantity = $quantity WHERE id = $cart_id";
if ($conn->query($sql) === TRUE) {
    echo "Quantity updated successfully.";
} else {
    echo "Error updating quantity: " . $conn->error;
}

$conn->close();
?>