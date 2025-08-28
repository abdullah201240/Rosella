<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection and cart functions
include '../db.php';
include 'includes/cart_functions.php';

// Start the session
session_start();

// Generate a unique session ID if it doesn't exist
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    // Get the product ID
    if (isset($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        
        // Fetch product details
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Add to cart
            $session_id = $_SESSION['session_id'];
            $success = addToCart($conn, $session_id, $product['id'], $product['name'], $product['price'], $product['image'], 1);
            
            if ($success) {
                // Get updated cart count
                $cart_count = getCartCount($conn, $session_id);
                
                $response['success'] = true;
                $response['message'] = 'Product added to cart successfully!';
                $response['cart_count'] = $cart_count;
            } else {
                $response['success'] = false;
                $response['message'] = 'Failed to add product to cart.';
            }
        } else {
            $response['success'] = false;
            $response['message'] = 'Product not found.';
        }
        
        $stmt->close();
    } else {
        $response['success'] = false;
        $response['message'] = 'Product ID is required.';
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// If not POST request, redirect to shop
header('Location: shop-grid.php');
exit();
?>
