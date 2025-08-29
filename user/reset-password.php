<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
session_start();

// Ensure we're using the correct database
$conn->select_db('womenClothing');

// Ensure password_resets table exists with correct structure
$table_check = $conn->query("SHOW TABLES LIKE 'password_resets'");
if ($table_check->num_rows == 0) {
    $create_table = $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        used TINYINT(1) DEFAULT 0,
        UNIQUE KEY (token)
    )");
    
    if ($create_table) {
        error_log("Successfully created password_resets table");
    } else {
        error_log("Error creating password_resets table: " . $conn->error);
    }
}

$message = '';
$message_type = '';
$code = '';
$valid_code = false;
$email = '';

// Check if we're in the code verification step
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $code = isset($_POST['code']) ? trim($_POST['code']) : '';
    // Prefer the email captured during the request step to avoid mismatches
    $emailFromSession = isset($_SESSION['reset_email']) ? trim($_SESSION['reset_email']) : '';
    $emailFromPost = isset($_POST['email']) ? trim($_POST['email']) : '';
    $email = $emailFromSession !== '' ? $emailFromSession : $emailFromPost;

    if ($code === '' || $email === '') {
        $message = 'Please enter the verification code and your email address.';
        $message_type = 'danger';
    } elseif (!preg_match('/^\d{6}$/', $code)) {
        $message = 'The verification code must be exactly 6 digits.';
        $message_type = 'danger';
    } else {
        // Verify the code and email pairing WITHOUT deleting first (avoid timezone-related premature deletions)
        error_log("Verifying reset code for email={$email}, code={$code}");
        $stmt = $conn->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ? AND email = ? LIMIT 1");
        $stmt->bind_param('ss', $code, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if ((int)$row['used'] === 1) {
                $message = 'This verification code has already been used. Please request a new one.';
                $message_type = 'danger';
            } elseif (strtotime($row['expires_at']) <= time()) {
                $message = 'This verification code has expired. Please request a new one.';
                $message_type = 'danger';
            } else {
                // Code is valid, mark it as used for this email and show password reset form
                $update = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ? AND email = ?");
                $update->bind_param('ss', $code, $email);
                $update->execute();
                $update->close();

                // Store in session and mark as verified
                $_SESSION['reset_code'] = $code;
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_code_verified'] = true;

                // Redirect to clear POST data
                header('Location: reset-password.php');
                exit();
            }
        } else {
            // Fallback: try to find by token only if unused and not expired
            $fallback = $conn->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ? AND used = 0 LIMIT 2");
            $fallback->bind_param('s', $code);
            $fallback->execute();
            $fbRes = $fallback->get_result();
            if ($fbRes && $fbRes->num_rows === 1) {
                $row = $fbRes->fetch_assoc();
                if (strtotime($row['expires_at']) > time() && (int)$row['used'] === 0) {
                    $email = $row['email'];
                    // Proceed as valid: mark used for this token+email and set session
                    $update = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ? AND email = ?");
                    $update->bind_param('ss', $code, $email);
                    $update->execute();
                    $update->close();

                    $_SESSION['reset_code'] = $code;
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_code_verified'] = true;

                    header('Location: reset-password.php');
                    exit();
                }
            }
            if ($fbRes && $fbRes->num_rows > 1) {
                error_log('Multiple rows found for same token; refusing fallback to avoid ambiguity.');
            }

            // Extra diagnostics to help identify mismatches
            $dbg1 = $conn->prepare("SELECT COUNT(*) AS c FROM password_resets WHERE token = ?");
            $dbg1->bind_param('s', $code);
            $dbg1->execute();
            $res1 = $dbg1->get_result();
            $countToken = $res1 ? ($res1->fetch_assoc()['c'] ?? 0) : 0;
            $dbg1->close();

            $dbg2 = $conn->prepare("SELECT COUNT(*) AS c FROM password_resets WHERE email = ?");
            $dbg2->bind_param('s', $email);
            $dbg2->execute();
            $res2 = $dbg2->get_result();
            $countEmail = $res2 ? ($res2->fetch_assoc()['c'] ?? 0) : 0;
            $dbg2->close();

            error_log("Reset verify failed. token_count={$countToken}, email_count={$countEmail}");
            $message = 'Invalid verification code. Please check and try again.';
            $message_type = 'danger';
        }
        $stmt->close();
    }
} elseif (isset($_SESSION['reset_code_verified']) && $_SESSION['reset_code_verified'] === true) {
    // User has already verified the code, show the password reset form
    $valid_code = true;
    $code = $_SESSION['reset_code'];
    $email = $_SESSION['reset_email'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']) && isset($_SESSION['reset_code']) && isset($_SESSION['reset_email'])) {
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $code = $_SESSION['reset_code'];
    $email = $_SESSION['reset_email'];
    
    if (empty($new_password) || empty($confirm_password)) {
        $message = 'Both password fields are required.';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $message_type = 'danger';
    } else {
        // Update the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param('ss', $hashed_password, $email);
        
        if ($update_stmt->execute()) {
            // Mark code as used only after successful password update
            $update = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ? AND email = ?");
            $update->bind_param('ss', $code, $email);
            $update->execute();
            $update->close();
            
            // Clear the session
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['code_expires']);
            
            $message = 'Your password has been updated successfully. You can now login with your new password.';
            $message_type = 'success';
            $valid_code = false; // Prevent form from being shown again
        } else {
            $message = 'An error occurred while updating your password. Please try again.';
            $message_type = 'danger';
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosella - Reset Password</title>
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
                        <h2>Reset Password</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container auth-container">
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="contact-form">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="contact__form__title">
                                <h2>Reset Password</h2>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Always show the code verification form first -->
                    <form action="" method="POST">
                        <input type="hidden" name="verify_code" value="1">
                        <div class="row">
                            <div class="col-lg-12">
                                <p>Please enter the 6-digit verification code sent to your email.</p>
                                <?php if (isset($_SESSION['reset_code_sent'])): ?>
                                    <div class="alert alert-info">
                                        Your verification code has been sent. Please check your email.
                                    </div>
                                    <?php unset($_SESSION['reset_code_sent']); ?>
                                <?php endif; ?>
                            </div>
                            <div class="col-lg-12">
                                <input type="text" name="code" placeholder="Enter 6-digit code" pattern="\d{6}" title="Please enter a 6-digit code" required>
                            </div>
                            <div class="col-lg-12">
                                <input type="email" name="email" placeholder="Your Email" value="<?php echo isset($_SESSION['reset_email']) ? htmlspecialchars($_SESSION['reset_email']) : ''; ?>" <?php echo isset($_SESSION['reset_email']) ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="col-lg-12 text-center">
                                <button type="submit" class="site-btn">Verify Code</button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Password Reset Form (hidden by default, shown after code verification) -->
                    <?php if (isset($_SESSION['reset_code_verified']) && $_SESSION['reset_code_verified'] === true): ?>
                    <div class="mt-5">
                        <h4>Set New Password</h4>
                        <form action="" method="POST">
                            <input type="hidden" name="reset_password" value="1">
                            <div class="row">
                                <div class="col-lg-12">
                                    <p>Please enter your new password below.</p>
                                </div>
                                <div class="col-lg-12">
                                    <input type="password" name="new_password" placeholder="New Password" required>
                                </div>
                                <div class="col-lg-12">
                                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                                </div>
                                <div class="col-lg-12 text-center">
                                    <button type="submit" class="site-btn">Reset Password</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-3">
                <a href="forgot-password.php" class="site-btn">Request New Reset Link</a>
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