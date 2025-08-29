<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
session_start();

// Ensure we're using the correct database
$conn->select_db('womenClothing');

// Ensure password_resets table exists
$create_table = $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    UNIQUE KEY (token)
)");

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $message = 'Please enter your email address.';
        $message_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'danger';
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate a 6-digit code (000000-999999)
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            
            // Debug: Log the code and expiration
            error_log("Generated code for $email: $code");
            error_log("Code will expire at: $expires");
            
            // Store the code in the database with detailed error checking
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (?, ?, ?, 0)");
            if ($stmt === false) {
                error_log("Prepare failed: " . $conn->error);
                $message = 'System error. Please try again later.';
                $message_type = 'danger';
                return;
            }
            
            $bind_result = $stmt->bind_param('sss', $email, $code, $expires);
            if ($bind_result === false) {
                error_log("Bind param failed: " . $stmt->error);
                $message = 'System error. Please try again later.';
                $message_type = 'danger';
                return;
            }
            
            $execute_result = $stmt->execute();
            if ($execute_result === false) {
                error_log("Execute failed: " . $stmt->error);
                $message = 'Failed to process your request. Please try again.';
                $message_type = 'danger';
                return;
            }
            
            // Verify the token was actually inserted
            $check = $conn->query("SELECT COUNT(*) as count FROM password_resets WHERE token = '" . $conn->real_escape_string($code) . "'");
            $row = $check->fetch_assoc();
            $token_stored = $row['count'] > 0;
            error_log("Token verification after storage: " . ($token_stored ? 'Success' : 'Failed'));
            
            if (!$token_stored) {
                error_log("Token was not saved to database");
                $message = 'Failed to process your request. Please try again.';
                $message_type = 'danger';
            } else {
                error_log("Verification code stored successfully for $email");
                // In a real application, you would send this code via email/SMS
                $reset_link = "http://".$_SERVER['HTTP_HOST']."/rosella/user/reset-password.php";
                $message = "<div style='text-align: center;'>
                    <h3>Password Reset Code</h3>
                    <p>Your password reset code is:</p>
                    <div style='font-size: 24px; font-weight: bold; margin: 15px 0;'>$code</div>
                    <p>Please go to the reset password page and enter this code:</p>
                    <p><a href='$reset_link' class='site-btn' style='display: inline-block; padding: 10px 20px;'>Go to Reset Password</a></p>
                    <p style='color: #888; margin-top: 20px;'>
                        The code will expire in 30 minutes.
                    </p>
                </div>";
                
                // Store the code in session for verification
                $_SESSION['reset_code'] = $code;
                $_SESSION['reset_email'] = $email;
                $_SESSION['code_expires'] = $expires;
                $_SESSION['reset_code_sent'] = true;
                $message_type = 'info';
            }
        } else {
            // For security, don't reveal if the email exists or not
            $message = 'If your email exists in our system, you will receive a password reset link.';
            $message_type = 'info';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosella - Forgot Password</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <style>
        .auth-container { max-width: 520px; margin: 30px auto; }
    </style>
</head>
<body>
    <div id="preloder"><div class="loader"></div></div>

    <?php include 'partials/header.php'; ?>

    <section class="breadcrumb-section set-bg" data-setbg="img/Frame3.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Forgot Password</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container auth-container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="checkout__form">
                <p>Enter your email address and we'll send you a link to reset your password.</p>
                <form method="POST" action="forgot-password.php">
                    <div class="checkout__input">
                        <p>Email<span>*</span></p>
                        <input type="email" name="email" required>
                    </div>
                    <button type="submit" class="site-btn">Send Reset Link</button>
                    <a href="login.php" class="site-btn" style="margin-left:10px;background:#6c757d;">Back to Login</a>
                </form>
            </div>
        </div>
    </section>

    <footer class='footer spad'>
        <div class='container'>
            <div class='row'>
                <div class='col-lg-3 col-md-6 col-sm-6'>
                    <div class='footer__about'>
                        <div class='footer__about__logo'>
                            <a href='./index.php'><img src='img/logo1.png' alt=''></a>
                        </div>
                        <ul>
                            <li>Address: Dhaka</li>
                            <li>Phone: 01800000000000</li>
                            <li>Email: hello.rosella54@gmail.com</li>
                        </ul>
                    </div>
                </div>
                <div class='col-lg-4 col-md-6 col-sm-6 offset-lg-1'>
                    <div class='footer__widget'>
                        <h6>Useful Links</h6>
                        <ul>
                            <li><a href='./index.php'>Home</a></li>
                            <li><a href='./shoping-cart.php'>Cart</a></li>
                            <li><a href='./contact.php'>Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class='col-lg-4 col-md-12'>
                    <div class='footer__widget'>
                        <h6>Contact Information</h6>
                        <p>We welcome your feedback on our customer service, merchandise, website, or any other topics
                            you wish to share with us. Your comments and suggestions are greatly appreciated.</p>
                        <div class='footer__widget__social'>
                            <a href='#'><i class='fa fa-facebook'></i></a>
                            <a href='#'><i class='fa fa-instagram'></i></a>
                            <a href='#'><i class='fa fa-twitter'></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/mixitup.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
