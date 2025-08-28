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

$sslcommerz = new SSLCommerz();
$order_id = '';
$tran_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate payment response from SSLCommerz
    $validation = $sslcommerz->validateResponse($_POST);
    
    if ($validation['status'] === 'cancelled') {
        $payment_status = 'cancelled';
        
        // Extract order ID from value_a
        $order_id = $_POST['value_a'] ?? '';
        
        if ($order_id) {
            // Update order status to cancelled
            $sql = "UPDATE orders SET 
                    status = 'cancelled', 
                    payment_status = 'cancelled',
                    updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
            
            // Clear SSLCommerz session data
            unset($_SESSION['sslcommerz_session_key']);
            unset($_SESSION['current_order_id']);
        }
    } else {
        // Handle other response types
        $payment_status = 'unknown';
        $order_id = $_POST['value_a'] ?? '';
        
        if ($order_id) {
            // Update order status to cancelled
            $sql = "UPDATE orders SET 
                    status = 'cancelled', 
                    payment_status = 'cancelled',
                    updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $stmt->close();
        }
    }
} else {
    // Redirect if accessed directly
    header("Location: checkout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Rosella</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">
    
    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    
    <style>
        .payment-result {
            padding: 60px 0;
            text-align: center;
        }
        .payment-icon {
            font-size: 80px;
            margin-bottom: 30px;
        }
        .cancelled-icon { color: #ffc107; }
        .payment-details {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
        }
        .btn-home {
            background: #7fad39;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-home:hover {
            background: #6b9a2f;
            color: white;
            text-decoration: none;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-warning:hover {
            background: #e0a800;
            color: #212529;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>
    
    <!-- Payment Result Section Begin -->
    <section class="payment-result">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="payment-icon cancelled-icon">
                        <i class="fa fa-ban"></i>
                    </div>
                    <h2 class="text-warning">Payment Cancelled!</h2>
                    <p class="lead">Your payment was cancelled. You can try again or continue shopping.</p>
                    
                    <div class="payment-details">
                        <h4>Payment Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_id); ?></p>
                                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($tran_id); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span class="text-warning">Cancelled</span></p>
                                <p><strong>Action:</strong> Payment was cancelled by user</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-details">
                        <h4>What happened?</h4>
                        <ul class="text-left">
                            <li>You cancelled the payment process</li>
                            <li>Your order is still in your cart</li>
                            <li>No charges were made to your account</li>
                            <li>You can complete the payment anytime</li>
                        </ul>
                    </div>
                    
                    <a href="index.php" class="btn-home">Continue Shopping</a>
                    <a href="checkout.php" class="btn-warning" style="margin-left: 15px;">Try Again</a>
                </div>
            </div>
        </div>
    </section>
    <!-- Payment Result Section End -->
    
    <!-- Footer Section Begin -->
    <footer class="footer spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="footer__about">
                        <div class="footer__about__logo">
                            <a href="./index.php"><img src="img/logo1.png" alt=""></a>
                        </div>
                        <ul>
                            <li>Address: Dhaka</li>
                            <li>Phone: 01800000000000</li>
                            <li>Email: hello.rosella54@gmail.com</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 offset-lg-1">
                    <div class="footer__widget">
                        <h6>Useful Links</h6>
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Cart</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="footer__widget">
                        <h6>Contact Information</h6>
                        <p>We welcome your feedback on our customer service, merchandise, website, or any other topics
                            you wish to share with us. Your comments and suggestions are greatly appreciated.</p>
                        <div class="footer__widget__social">
                            <a href="#"><i class="fa fa-facebook"></i></a>
                            <a href="#"><i class="fa fa-instagram"></i></a>
                            <a href="#"><i class="fa fa-twitter"></i></a>
                            <a href="#"><i class="fa fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer__copyright">
                        <div class="footer__copyright__text">
                            <p>
                                Copyright &copy;
                                <script>document.write(new Date().getFullYear());</script> All rights reserved
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Footer Section End -->
    
    <!-- Js Plugins -->
    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
