<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate a unique session ID if it doesn't exist
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Include cart functions
include_once 'includes/cart_functions.php';

// Initialize cart count
$cart_count = 0;

// Only try to get cart count if we have a valid database connection
if (isset($GLOBALS['conn']) && $GLOBALS['conn'] && !$GLOBALS['conn']->connect_error) {
    $cart_count = getCartCount($GLOBALS['conn'], $_SESSION['session_id']);
} else {
    error_log("Warning: No valid database connection in header.php");
}

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentScript = basename($currentPath);

$isHome = $currentScript === 'index.php';
$isShop = in_array($currentScript, ['shop-grid.php', 'shop-details.php']);
$isCart = $currentScript === 'shoping-cart.php';
$isContact = $currentScript === 'contact.php';
$isLogin = $currentScript === 'login.php';
$isSignup = $currentScript === 'signup.php';
$isProfile = $currentScript === 'profile.php';
$isMyOrders = $currentScript === 'my_orders.php';

$redirectTarget = $currentScript;
?>

<!-- Humberger Begin -->
<div class="humberger__menu__overlay"></div>
<div class="humberger__menu__wrapper">
    <div class="humberger__menu__logo">
        <a href="./index.php"><img src="img/logo1.png" alt=""></a>
    </div>
    <nav class="humberger__menu__nav mobile-menu">
        <ul>
            <li class='<?php echo $isHome ? 'active' : '';
?>'><a href='./index.php'>Home</a></li>
            <li class='<?php echo $isShop ? 'active' : '';
?>'><a href='./shop-grid.php'>Shop</a></li>
                                    <li class='<?php echo $isCart ? 'active' : '';
?>'><a href='./shoping-cart.php'>Shoping Cart <span class="cart-count" id="cart-count-nav-desktop"><?php echo $cart_count; ?></span></a></li>
            <li class='<?php echo $isContact ? 'active' : '';
?>'><a href='./contact.php'>Contact</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class='<?php echo $isProfile ? 'active' : '';
?>'><a href='./profile.php'>Profile</a></li>
                <li class='<?php echo $isMyOrders ? 'active' : '';
?>'><a href='./my_orders.php'>My Orders</a></li>
                <li><a href='./logout.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Logout (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
            <?php else: ?>
                <li class='<?php echo $isLogin ? 'active' : '';
?>'><a href='./login.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Login</a></li>
                <li class='<?php echo $isSignup ? 'active' : '';
?>'><a href='./signup.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <div id="mobile-menu-wrap"></div>
</div>
<!-- Humberger End -->

<!-- Header Section Begin -->
<header class='header'>
    <div class='container'>
        <div class='row'>
        <div class='col-lg-3'>
    <div class='header__logo'>
        <a href='./index.php'><img src='img/logo1.png' alt=''></a>
    </div>
</div>

            <div class='col-lg-9'>
                <nav class='header__menu'>
                    <ul>
                        <li class='<?php echo $isHome ? 'active' : '';
?>'><a href='./index.php'>Home</a></li>
                        <li class='<?php echo $isShop ? 'active' : '';
?>'><a href='./shop-grid.php'>Shop</a></li>
                        <li class='<?php echo $isCart ? 'active' : '';
?>'><a href='./shoping-cart.php'>Cart <span class="cart-count" id="cart-count-nav"><?php echo $cart_count; ?></span></a></li>
                        <li class='<?php echo $isContact ? 'active' : '';
?>'><a href='./contact.php'>Contact</a></li>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li class='<?php echo $isProfile ? 'active' : '';
?>'><a href='./profile.php'>Profile</a></li>
                            <li class='<?php echo $isMyOrders ? 'active' : '';
?>'><a href='./my_orders.php'>My Orders</a></li>
                            <li><a href='./logout.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Logout</a></li>
                        <?php else: ?>
                            <li class='<?php echo $isLogin ? 'active' : '';
?>'><a href='./login.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Login</a></li>
                            <li class='<?php echo $isSignup ? 'active' : '';
?>'><a href='./signup.php?redirect = <?php echo urlencode( $redirectTarget );
?>'>Sign Up</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
        <div class='humberger__open'>
            <i class='fa fa-bars'></i>
</div>
</div>
</header>
<!-- Header Section End -->

<!-- WhatsApp Floating Button -->
<style>
.whatsapp-float {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 56px;
    height: 56px;
    background: #25D366;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    box-shadow: 0 2px 8px rgba( 0, 0, 0, .2 );
    z-index: 9999;
    text-decoration: none;
}
.whatsapp-float:hover {
    background: #1ebe57;
    color: #fff;
}
@media ( max-width: 576px ) {
    .whatsapp-float {
        bottom: 16px;
        right: 16px;
        width: 52px;
        height: 52px;
        font-size: 26px;
    }
}

/* Cart count badge styling */
.cart-count {
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 5px;
    display: inline-block;
    min-width: 18px;
    text-align: center;
    line-height: 14px;
}

.cart-count:empty {
    display: none;
}
.header .row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

</style>
<a href = 'https://wa.me/8801617980079' class = 'whatsapp-float' target = '_blank' rel = 'noopener' aria-label = 'Chat on WhatsApp'>
<i class = 'fa fa-whatsapp'></i>
</a>
