<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Get the cart ID from the POST request
$cart_id = intval($_POST['cart_id']);

// Delete the item from the cart
$sql = "DELETE FROM carts WHERE id = $cart_id";
if ($conn->query($sql) === TRUE) {
    echo "Item removed successfully.";
} else {
    echo "Error removing item: " . $conn->error;
}

$conn->close();

// Redirect back to the cart page
header("Location: shoping-cart.php");
exit();
?>