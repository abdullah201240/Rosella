<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode('profile.php'));
    exit();
}

// Ensure profile table exists
$conn->query("CREATE TABLE IF NOT EXISTS user_profiles (
    user_id INT PRIMARY KEY,
    first_name VARCHAR(100) NULL,
    last_name VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    address VARCHAR(255) NULL,
    address2 VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    postcode VARCHAR(50) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(255) NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$uid = (int)$_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['first_name'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $address2 = trim($_POST['address2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $sql = "INSERT INTO user_profiles (user_id, first_name, last_name, country, address, address2, city, state, postcode, phone, email)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE first_name=VALUES(first_name), last_name=VALUES(last_name), country=VALUES(country), address=VALUES(address), address2=VALUES(address2), city=VALUES(city), state=VALUES(state), postcode=VALUES(postcode), phone=VALUES(phone), email=VALUES(email)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issssssssss', $uid, $first_name, $last_name, $country, $address, $address2, $city, $state, $postcode, $phone, $email);
    if ($stmt->execute()) {
        $message = 'Profile updated successfully.';
    } else {
        $message = 'Failed to update profile. Please try again.';
    }
    $stmt->close();
}

// Load profile data
$profile = [
    'first_name' => '', 'last_name' => '', 'country' => '', 'address' => '', 'address2' => '', 'city' => '', 'state' => '', 'postcode' => '', 'phone' => '', 'email' => ''
];
$res = $conn->query("SELECT * FROM user_profiles WHERE user_id = $uid LIMIT 1");
if ($res && $res->num_rows === 1) {
    $profile = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rosella - Profile</title>
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
        .profile-container { max-width: 720px; margin: 30px auto; }
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
                        <h2>My Profile</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container profile-container">
            <?php if ($message !== ''): ?>
                <div class="alert alert-info" role="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <div class="checkout__form">
                <h4>Profile & Address</h4>
                <form method="POST" action="profile.php">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>First Name<span>*</span></p>
                                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($profile['first_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Last Name<span>*</span></p>
                                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($profile['last_name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <div class="checkout__input">
                                <p>Country<span>*</span></p>
                                <input type="text" name="country" value="<?php echo htmlspecialchars($profile['country'] ?? ''); ?>" required>
                            </div>
                            <div class="checkout__input">
                                <p>Address<span>*</span></p>
                                <input type="text" name="address" placeholder="Street Address" class="checkout__input__add" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>" required>
                                <input type="text" name="address2" placeholder="Apartment, suite, unit etc (optional)" value="<?php echo htmlspecialchars($profile['address2'] ?? ''); ?>">
                            </div>
                            <div class="checkout__input">
                                <p>Town/City<span>*</span></p>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>" required>
                            </div>
                            <div class="checkout__input">
                                <p>Country/State<span>*</span></p>
                                <input type="text" name="state" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>" required>
                            </div>
                            <div class="checkout__input">
                                <p>Postcode / ZIP<span>*</span></p>
                                <input type="text" name="postcode" value="<?php echo htmlspecialchars($profile['postcode'] ?? ''); ?>" required>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Phone<span>*</span></p>
                                        <input type="text" name="phone" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Email<span>*</span></p>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="submit" class="site-btn">Save Profile</button>
                        <a href="checkout.php" class="site-btn" style="margin-left:10px;background:#28a745;">Go to Checkout</a>
                    </div>
                </form>

                <!-- Password Change Form -->
                <div class="mt-5">
                    <h4>Change Password</h4>
                    <?php
                    if (isset($_SESSION['password_message'])) {
                        $alert_class = $_SESSION['password_message_type'] === 'success' ? 'alert-success' : 'alert-danger';
                        echo '<div class="alert ' . $alert_class . '" role="alert">' . $_SESSION['password_message'] . '</div>';
                        unset($_SESSION['password_message']);
                        unset($_SESSION['password_message_type']);
                    }
                    ?>
                    <form method="POST" action="update_password.php" class="mt-4">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="checkout__input">
                                    <p>Current Password<span>*</span></p>
                                    <input type="password" name="current_password" required>
                                </div>
                                <div class="checkout__input">
                                    <p>New Password<span>*</span></p>
                                    <input type="password" name="new_password" required>
                                </div>
                                <div class="checkout__input">
                                    <p>Confirm New Password<span>*</span></p>
                                    <input type="password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="site-btn">Update Password</button>
                            </div>
                        </div>
                    </form>
                </div>
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
                            <a href='#'><i class='fa fa-pinterest'></i></a>
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


