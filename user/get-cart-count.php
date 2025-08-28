<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include '../db.php';

// Include cart functions
include 'includes/cart_functions.php';

// Initialize response array
$response = ['count' => 0];

// Check if we have a valid session and database connection
if (isset($_SESSION['session_id']) && isset($conn) && $conn && !$conn->connect_error) {
    $response['count'] = getCartCount($conn, $_SESSION['session_id']);
}

// Return the count as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
