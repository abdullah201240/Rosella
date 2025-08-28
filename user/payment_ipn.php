<?php
/**
 * SSLCommerz IPN (Instant Payment Notification) Handler
 * This file handles server-to-server notifications from SSLCommerz
 */

// Include the database connection and SSLCommerz class
include '../db.php';
include '../includes/SSLCommerz.php';

// Start the session
session_start();

// Log IPN data for debugging
$ipn_log = date('Y-m-d H:i:s') . " - IPN Received\n";
$ipn_log .= "POST Data: " . print_r($_POST, true) . "\n";
$ipn_log .= "GET Data: " . print_r($_GET, true) . "\n";
$ipn_log .= "----------------------------------------\n";

// Write to log file
file_put_contents('../logs/ipn.log', $ipn_log, FILE_APPEND | LOCK_EX);

$sslcommerz = new SSLCommerz();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate IPN data
    $validation = $sslcommerz->validateResponse($_POST);
    
    if ($validation['status'] === 'success') {
        $tran_id = $validation['tran_id'];
        $val_id = $validation['val_id'];
        $amount = $validation['amount'];
        $order_id = $_POST['value_a'] ?? '';
        
        if ($order_id) {
            // Update order status to paid
            $sql = "UPDATE orders SET 
                    status = 'paid', 
                    payment_transaction_id = ?, 
                    payment_amount = ?, 
                    payment_date = NOW(),
                    payment_method = 'SSLCommerz',
                    payment_status = 'completed'
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdi", $tran_id, $amount, $order_id);
            
            if ($stmt->execute()) {
                // Log successful update
                $success_log = date('Y-m-d H:i:s') . " - Order #$order_id updated successfully\n";
                file_put_contents('../logs/ipn.log', $success_log, FILE_APPEND | LOCK_EX);
                
                // Send confirmation email (optional)
                // sendOrderConfirmationEmail($order_id);
                
                // Clear cart if session exists
                if (isset($_SESSION['session_id'])) {
                    $session_id = $_SESSION['session_id'];
                    $sql_clear_cart = "DELETE FROM carts WHERE session_id = ?";
                    $stmt_clear_cart = $conn->prepare($sql_clear_cart);
                    $stmt_clear_cart->bind_param("s", $session_id);
                    $stmt_clear_cart->execute();
                    $stmt_clear_cart->close();
                }
                
                $stmt->close();
                
                // Return success response to SSLCommerz
                http_response_code(200);
                echo "OK";
                exit();
            } else {
                // Log error
                $error_log = date('Y-m-d H:i:s') . " - Error updating order #$order_id: " . $stmt->error . "\n";
                file_put_contents('../logs/ipn.log', $error_log, FILE_APPEND | LOCK_EX);
                
                $stmt->close();
                http_response_code(500);
                echo "ERROR";
                exit();
            }
        } else {
            // Log missing order ID
            $error_log = date('Y-m-d H:i:s') . " - Missing order ID in IPN\n";
            file_put_contents('../logs/ipn.log', $error_log, FILE_APPEND | LOCK_EX);
            
            http_response_code(400);
            echo "MISSING_ORDER_ID";
            exit();
        }
    } else {
        // Log validation failure
        $error_log = date('Y-m-d H:i:s') . " - IPN validation failed: " . $validation['message'] . "\n";
        file_put_contents('../logs/ipn.log', $error_log, FILE_APPEND | LOCK_EX);
        
        http_response_code(400);
        echo "VALIDATION_FAILED";
        exit();
    }
} else {
    // Log invalid request method
    $error_log = date('Y-m-d H:i:s') . " - Invalid request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
    file_put_contents('../logs/ipn.log', $error_log, FILE_APPEND | LOCK_EX);
    
    http_response_code(405);
    echo "METHOD_NOT_ALLOWED";
    exit();
}

/**
 * Send order confirmation email (optional function)
 */
function sendOrderConfirmationEmail($order_id) {
    // Implementation for sending confirmation email
    // You can integrate with your preferred email service
    // For now, this is just a placeholder
}
?>
