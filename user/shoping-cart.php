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

// Calculate subtotal
$subtotal = 0;
while ($cart_item = $result_cart->fetch_assoc()) {
    $subtotal += $cart_item['product_price'] * $cart_item['quantity'];
}
$total = $subtotal; // Assuming no taxes or shipping for now
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rosella</title>
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
                        <h2>Shopping Cart</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Shoping Cart Section Begin -->
    <section class="shoping-cart spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="shoping__cart__table">
                        <table>
                            <thead>
                                <tr>
                                    <th class="shoping__product">Products</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_cart->num_rows > 0): ?>
                                    <?php $result_cart->data_seek(0); // Reset pointer ?>
                                    <?php while ($cart_item = $result_cart->fetch_assoc()): ?>
                                        <tr>
                                            <td class="shoping__cart__item">
                                                <img src="../uploads/<?php echo $cart_item['product_image']; ?>" alt="<?php echo $cart_item['product_name']; ?>" style="width: 50px; height: 50px;">
                                                <h5><?php echo $cart_item['product_name']; ?></h5>
                                            </td>
                                            <td class="shoping__cart__price">
                                                ৳<?php echo $cart_item['product_price']; ?>
                                            </td>
                                            <td class="shoping__cart__quantity">
                                                <div class="quantity">
                                                    <div class="pro-qty">
                                                        <input type="text" value="<?php echo $cart_item['quantity']; ?>" data-cart-id="<?php echo $cart_item['id']; ?>">
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="shoping__cart__total">
                                                ৳<?php echo $cart_item['product_price'] * $cart_item['quantity']; ?>
                                            </td>
                                            <td class="shoping__cart__item__close">
                                                <form method="POST" action="remove-from-cart.php">
                                                    <input type="hidden" name="cart_id" value="<?php echo $cart_item['id']; ?>">
                                                    <button type="submit" class="btn btn-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">Your cart is empty.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="shoping__checkout">
                        <h5>Cart Total</h5>
                        <ul>
                            <li>Subtotal <span>৳<?php echo $subtotal; ?></span></li>
                            <li>Total <span>৳<?php echo $total; ?></span></li>
                        </ul>
                        <a href="checkout.php" class="primary-btn">PROCEED TO CHECKOUT</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Shoping Cart Section End -->

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

    <!-- Custom Script for Quantity Update -->
 <script>
    $(document).ready(function() {
        // Initialize quantity buttons
        $('.pro-qty').each(function() {
            var $this = $(this),
                $input = $this.find('input[type="text"]'),
                $increase = $this.find('.inc'),
                $decrease = $this.find('.dec');

            $increase.on('click', function() {
                var currentVal = parseInt($input.val());
                if (!isNaN(currentVal)) {
                    $input.val(currentVal + 1).trigger('change');
                }
            });

            $decrease.on('click', function() {
                var currentVal = parseInt($input.val());
                if (!isNaN(currentVal) && currentVal > 1) {
                    $input.val(currentVal - 1).trigger('change');
                }
            });
        });

        // Handle quantity change
        $('.pro-qty input').on('change', function() {
            let quantity = $(this).val();
            let cartId = $(this).data('cart-id');

            // Send AJAX request to update quantity
            $.ajax({
                url: 'update-cart-quantity.php',
                method: 'POST',
                data: {
                    cart_id: cartId,
                    quantity: quantity
                },
                success: function(response) {
                    location.reload(); // Refresh the page to reflect changes
                },
                error: function(xhr, status, error) {
                    alert('Failed to update quantity. Please try again.');
                }
            });
        });
    });
</script>
</body>

</html>