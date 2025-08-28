
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Start the session
session_start();

// Allow guest checkout; no forced login here

// Generate a unique session ID if it doesn't exist
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Fetch cart data from the database
$session_id = $_SESSION['session_id'];
$sql_cart = "SELECT * FROM carts WHERE session_id = '$session_id'";
$result_cart = $conn->query($sql_cart);

// Initialize $cart_items array
$cart_items = [];

// Calculate subtotal and populate $cart_items
$subtotal = 0;
while ($cart_item = $result_cart->fetch_assoc()) {
    $cart_items[] = $cart_item; // Add each cart item to the $cart_items array
    $subtotal += $cart_item['product_price'] * $cart_item['quantity'];
}
$total_amount = $subtotal; // Assuming no taxes or shipping for now
// Ensure user_profiles table exists
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

// Fetch saved profile for prefill
$profile = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $resProfile = $conn->query("SELECT * FROM user_profiles WHERE user_id = $uid LIMIT 1");
    if ($resProfile && $resProfile->num_rows === 1) {
        $profile = $resProfile->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rosella - Checkout</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap" rel="stylesheet">

    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>

<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    <?php include 'partials/header.php'; ?>

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="img/Frame3.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                        <h2>Checkout</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Checkout Section Begin -->
    <section class="checkout spad">
        <div class="container">
            <div class="checkout__form">
                <h4>Billing Details</h4>
                <form action="checkout_process.php" method="POST" id="checkoutForm">
                    <div class="row">
                        <div class="col-lg-8 col-md-6">
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
                                <input type="text" name="state" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>" required>
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
                            <div class="checkout__input">
                                <p>Order notes</p>
                                <input type="text" name="order_notes" placeholder="Notes about your order, e.g. special notes for delivery.">
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="checkout__order">
                                <h4>Your Order</h4>
                                <div class="checkout__order__products">Products <span>Total</span></div>
                                <ul>
                                    <?php foreach ($cart_items as $item): ?>
                                        <li>
                                            <?php echo $item['product_name']; ?> 
                                            <span>৳<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?></span>
                                            <br>
                                            <small>Quantity: <?php echo $item['quantity']; ?></small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <div class="checkout__order__subtotal">Subtotal <span>৳<?php echo number_format($total_amount, 2); ?></span></div>
                                <div class="checkout__order__total">Total <span>৳<?php echo number_format($total_amount, 2); ?></span></div>
                                <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
                                <input type="hidden" name="products" value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">
                                <button type="submit" class="site-btn" id="placeOrderBtn">
                                    <i class="fa fa-credit-card"></i> PROCEED TO PAYMENT
                                </button>
                                <p class="text-muted mt-2">
                                    <small>You will be redirected to SSLCommerz secure payment gateway</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- Checkout Section End -->

    <!-- Footer Section Begin -->
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
                            <li><a href='#'>Home</a></li>
                            <li><a href='#'>Cart</a></li>
                            <li><a href='#'>Contact</a></li>
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
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='footer__copyright'>
                        <div class='footer__copyright__text'>
                            <p>
                                Copyright &copy;
                                <script>document.write(new Date().getFullYear());
                                </script> All rights reserved </a>
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
    <script src="js/jquery.nice-select.min.js"></script>
    <script src="js/jquery-ui.min.js"></script>
    <script src="js/jquery.slicknav.js"></script>
    <script src="js/mixitup.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        $(document).ready(function() {
            // Form validation and submission handling
            $('#checkoutForm').on('submit', function(e) {
                var $form = $(this);
                var $btn = $('#placeOrderBtn');
                var originalText = $btn.html();
                
                // Basic validation
                var requiredFields = $form.find('[required]');
                var isValid = true;
                
                requiredFields.each(function() {
                    if (!$(this).val().trim()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                    return false;
                }
                
                // Show loading state
                $btn.prop('disabled', true);
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                
                // Add loading overlay
                $('body').append('<div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;"><div class="text-white text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><br><h4 class="mt-3">Processing your order...</h4><p>Please wait while we redirect you to the payment gateway.</p></div></div>');
                
                // Form will submit normally
                return true;
            });
            
            // Remove loading state if form validation fails
            $('input[required]').on('input', function() {
                if ($(this).val().trim()) {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Check if cart is empty
            <?php if (empty($cart_items)): ?>
            $('#placeOrderBtn').prop('disabled', true).html('Cart is Empty');
            <?php endif; ?>
        });
    </script>
</body>

</html>