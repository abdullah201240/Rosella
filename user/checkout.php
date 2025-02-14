
<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Start the session
session_start();

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

    <!-- Header Section Begin -->
    <header class="header">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <div class="header__logo">
                        <a href="./index.php"><img src="img/logo.png" alt=""></a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <nav class="header__menu">
                        <ul>
                            <li class='active'><a href='./index.php'>Home</a></li>
                            <li><a href='./shop-grid.php'>Shop</a></li>
                            <li><a href='./shoping-cart.php'>Shoping Cart</a></li>
                            <li><a href='./contact.php'>Contact</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="humberger__open">
                <i class="fa fa-bars"></i>
            </div>
        </div>
    </header>
    <!-- Header Section End -->

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
                <form action="checkout_process.php" method="POST">
                    <div class="row">
                        <div class="col-lg-8 col-md-6">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>First Name<span>*</span></p>
                                        <input type="text" name="first_name" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Last Name<span>*</span></p>
                                        <input type="text" name="last_name" required>
                                    </div>
                                </div>
                            </div>
                            <div class="checkout__input">
                                <p>Country<span>*</span></p>
                                <input type="text" name="country" required>
                            </div>
                            <div class="checkout__input">
                                <p>Address<span>*</span></p>
                                <input type="text" name="address" placeholder="Street Address" class="checkout__input__add" required>
                                <input type="text" name="address2" placeholder="Apartment, suite, unit etc (optional)">
                            </div>
                            <div class="checkout__input">
                                <p>Town/City<span>*</span></p>
                                <input type="text" name="city" required>
                            </div>
                            <div class="checkout__input">
                                <p>Country/State<span>*</span></p>
                                <input type="text" name="state" required>
                            </div>
                            <div class="checkout__input">
                                <p>Postcode / ZIP<span>*</span></p>
                                <input type="text" name="postcode" required>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Phone<span>*</span></p>
                                        <input type="text" name="phone" required>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="checkout__input">
                                        <p>Email<span>*</span></p>
                                        <input type="email" name="email" required>
                                    </div>
                                </div>
                            </div>
                            <div class="checkout__input">
                                <p>Order notes<span>*</span></p>
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
                    <span>$<?php echo number_format($item['product_price'] * $item['quantity'], 2); ?></span>
                    <br>
                    <small>Quantity: <?php echo $item['quantity']; ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="checkout__order__subtotal">Subtotal <span>$<?php echo number_format($total_amount, 2); ?></span></div>
        <div class="checkout__order__total">Total <span>$<?php echo number_format($total_amount, 2); ?></span></div>
        <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
        <input type="hidden" name="products" value="<?php echo htmlspecialchars(json_encode($cart_items)); ?>">
        <button type="submit" class="site-btn">PLACE ORDER</button>
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
                            <li>Address: 60-49 Road 11378 New York</li>
                            <li>Phone: +65 11.188.888</li>
                            <li>Email: hello@colorlib.com</li>
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
</body>

</html>