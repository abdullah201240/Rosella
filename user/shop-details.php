<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Start the session
session_start();
// Include the database connection
include '../db.php';

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize the input

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
} else {
    die("Invalid request.");
}
?>
<?php
// Fetch related products (products in the same category, excluding the current product)
$category_id = $product['category_id']; // Get the current product's category ID
$sql_related = "SELECT * FROM products WHERE category_id = $category_id  LIMIT 4"; // Limit to 4 related products
$result_related = $conn->query($sql_related);
?>
<?php


// Generate a unique session ID if it doesn't exist
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']); // Sanitize the input

    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        die("Product not found.");
    }
} else {
    die("Invalid request.");
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $session_id = $_SESSION['session_id'];
    $product_id = $product['id'];
    $product_name = $product['name'];
    $product_price = $product['price'];
    $product_image = $product['image'];
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1; // Get quantity from form

    // Check if the product is already in the cart for this session
    $sql_check = "SELECT * FROM carts WHERE session_id = '$session_id' AND product_id = $product_id";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        // Update the quantity if the product is already in the cart
        $sql_update = "UPDATE carts SET quantity = quantity + $quantity WHERE session_id = '$session_id' AND product_id = $product_id";
        $conn->query($sql_update);
    } else {
        // Insert the product into the cart
        $sql_insert = "INSERT INTO carts (session_id, product_id, product_name, product_price, product_image, quantity)
                       VALUES ('$session_id', $product_id, '$product_name', $product_price, '$product_image', $quantity)";
        $conn->query($sql_insert);
    }

    // Set a session variable to indicate success
    $_SESSION['cart_success'] = true;

    // Redirect to avoid form resubmission
    header("Location: shop-details.php?id=$product_id");
    exit();
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
    <title><?php echo $product['name']; ?> - Product Details</title>
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

    <!-- Humberger Begin -->
    <div class="humberger__menu__overlay"></div>
   
    <!-- Humberger End -->

    <!-- Header Section Begin -->
    <header class="header">
        
    <div class='container'>
            <div class='row'>
                <div class='col-lg-3'>
                    <div class='header__logo'>
                        <a href='./index.php'><img src='img/logo1.png' alt=''></a>
                    </div>
                </div>
                <div class='col-lg-6'>
                    <nav class='header__menu'>
                        <ul>
                            <li class='active'><a href='./index.php'>Home</a></li>
                            <li><a href='./shop-grid.php'>Shop</a></li>

                            <li><a href='./shoping-cart.php'>Shoping Cart</a></li>

                            <li><a href='./contact.php'>Contact</a></li>
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

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="img/Frame3.png">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <div class="breadcrumb__text">
                    <h2><?php echo $product['name']; ?></h2>

                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Product Details Section Begin -->
    <section class="product-details spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__pic">
                        <div class="product__details__pic__item">
                            <img class="product__details__pic__item--large"
                            src='../uploads/<?php echo $product['image']; ?>' alt="product image">
                        </div>
                        
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="product__details__text">
                        <h3><?php echo $product['name']; ?></h3>
                        
                        <div class="product__details__price">৳<?php echo $product['price']; ?></div>
                        <p><?php echo $product['description']; ?></p>
                        <div class="product__details__quantity">
    <div class="quantity">
        <div class="pro-qty">
            <input type="text" id="quantity" name="quantity" value="1">
        </div>
    </div>
</div>

<form method="POST" action="">
    <input type="hidden" name="add_to_cart" value="1">
    <input type="hidden" id="quantity_hidden" name="quantity" value="1">
    <button type="submit" class="primary-btn">ADD TO CART</button>
</form>
                    
                        <ul>
                            <li><b>Availability</b> <span>In Stock</span></li>
                            <li><b>Shipping</b> <span>01 day shipping. <samp>Free pickup today</samp></span></li>
                            <li><b>Weight</b> <span><?php echo $product['weight']; ?> g</span></li>
                            
                        </ul>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="product__details__tab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tabs-1" role="tab"
                                    aria-selected="true">Description</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tabs-2" role="tab"
                                    aria-selected="false">Information</a>
                            </li>
                            
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tabs-1" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <h6>Products Description</h6>
                                    <p>  <?php echo $product['description']; ?></p>
                                </div>
                            </div>
                            <div class="tab-pane" id="tabs-2" role="tabpanel">
                                <div class="product__details__tab__desc">
                                    <h6>Products Infomation</h6>
                                    <p><strong>Product Code:</strong> <?php echo $product['product_code']; ?></p>
                                    <p><strong>Price:</strong> ৳<?php echo $product['price']; ?></p>
                                    <p><strong>Weight:</strong> <?php echo $product['weight']; ?></p>
                                    <p><strong>Care Note:</strong> <?php echo $product['care_note']; ?></p>


                                </div>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Details Section End -->

    <!-- Related Product Section Begin -->
<section class="related-product">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title related__product__title">
                    <h2>Related Product</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <?php if ($result_related->num_rows > 0): ?>
                <?php while ($related_product = $result_related->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product__item">
                            <div class="product__item__pic set-bg" data-setbg="../uploads/<?php echo $related_product['image']; ?>">
                                <ul class="product__item__pic__hover">
                                    <li><a href="#"><i class="fa fa-heart"></i></a></li>
                                    <li><a href="#"><i class="fa fa-retweet"></i></a></li>
                                    <li><a href="shop-details.php?id=<?php echo $related_product['id']; ?>"><i class="fa fa-shopping-cart"></i></a></li>
                                </ul>
                            </div>
                            <div class="product__item__text">
                                <h6><a href="shop-details.php?id=<?php echo $related_product['id']; ?>"><?php echo $related_product['name']; ?></a></h6>
                                <h5>৳<?php echo $related_product['price']; ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <p>No related products found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php if (isset($_SESSION['cart_success'])): ?>
        <script>
            alert('Product added to cart successfully!');
        </script>
        <?php unset($_SESSION['cart_success']); // Clear the session variable ?>
    <?php endif; ?>
<!-- Related Product Section End -->
    <!-- Related Product Section End -->

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
                            <li><a href='#'>
                                    Contact</a></li>

                        </ul>

                    </div>
                </div>
                <div class='col-lg-4 col-md-12'>
                    <div class='footer__widget'>
                        <h6>Contact Information
                        </h6>
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
            // Initialize quantity input
            var quantityInput = $('#quantity');
            var proQty = $('.pro-qty');

            // Increase quantity
            proQty.on('click', '.qtybtn.up', function() {
                var currentVal = parseInt(quantityInput.val());
                if (!isNaN(currentVal)) {
                    quantityInput.val(currentVal + 1);
                }
            });

            // Decrease quantity
            proQty.on('click', '.qtybtn.down', function() {
                var currentVal = parseInt(quantityInput.val());
                if (!isNaN(currentVal) && currentVal > 1) {
                    quantityInput.val(currentVal - 1);
                }
            });

            // Update hidden input field before form submission
            $('form').on('submit', function() {
                var quantity = $('#quantity').val();
                $('#quantity_hidden').val(quantity);
            });
        });
    </script>

</body>

</html>