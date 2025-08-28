<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Start the session
session_start();

$order_id = $_GET['order_id'] ?? '';
$error_message = $_GET['error'] ?? 'An unknown error occurred';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error - Rosella</title>
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
        .error-icon { color: #dc3545; }
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
        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        .btn-danger:hover {
            background: #c82333;
            color: white;
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
                    <div class="payment-icon error-icon">
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                    <h2 class="text-danger">Payment Gateway Error!</h2>
                    <p class="lead">We encountered an issue while processing your payment. Please try again.</p>
                    
                    <div class="payment-details">
                        <h4>Error Details</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Order ID:</strong> #<?php echo htmlspecialchars($order_id); ?></p>
                                <p><strong>Error Type:</strong> Gateway Connection Error</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> <span class="text-danger">Failed</span></p>
                                <p><strong>Message:</strong> <?php echo htmlspecialchars($error_message); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="payment-details">
                        <h4>What to do next?</h4>
                        <ul class="text-left">
                            <li>Check your internet connection</li>
                            <li>Try again in a few minutes</li>
                            <li>Contact customer support if the problem persists</li>
                            <li>Your order is saved and you can retry the payment</li>
                        </ul>
                    </div>
                    
                    <a href="index.php" class="btn-home">Continue Shopping</a>
                    <a href="checkout.php" class="btn-danger" style="margin-left: 15px;">Try Again</a>
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
