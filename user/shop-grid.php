<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start the session first
session_start();

// Log session start
error_log("Session started: " . session_id());

// Generate a unique session ID if it doesn't exist
if (!isset($_SESSION['session_id'])) {
    $_SESSION['session_id'] = session_id();
}

// Include database connection
$conn = null;
try {
    include_once '../db.php';
    error_log("Database connection included successfully");
    
    // Test the connection
    if ($conn && $conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Error connecting to database. Please try again later.");
}

// Include cart functions
if (file_exists('includes/cart_functions.php')) {
    include_once 'includes/cart_functions.php';
    error_log("Cart functions included successfully");
} else {
    error_log("Error: cart_functions.php not found");
    die("Required system files are missing. Please contact support.");
}

// Fetch categories
$sql_categories = "SELECT * FROM categories"; 
$result_categories = $conn->query($sql_categories);
if (!$result_categories) {
    die("Category query failed: " . $conn->error);
}

// Determine the sort option
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'all';

// Determine the selected category
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Build product query
$sql_products = "SELECT * FROM products WHERE 1=1";

// Add category filter if a category is selected
if ($category_id) {
    $sql_products .= " AND category_id = $category_id";
}

// Add sorting
switch ($sort_option) {
    case 'price_high_to_low':
        $sql_products .= " ORDER BY price DESC";
        break;
    case 'price_low_to_high':
        $sql_products .= " ORDER BY price ASC";
        break;
    case 'latest':
        $sql_products .= " ORDER BY id DESC";
        break;
    default:
        $sql_products .= " ORDER BY id ASC";
        break;
}

// Add LIMIT for non-"all" sorts
if ($sort_option !== 'all') {
    $sql_products .= " LIMIT 20";
}

$result_products = $conn->query($sql_products);
if (!$result_products) {
    die("Product query failed: " . $conn->error);
}

$latestProducts = [];
while ($row = $result_products->fetch_assoc()) {
    $latestProducts[] = $row;
}

// Database connection will be closed at the end of the script
?>

<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <title>Rosella</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
    <!-- Page Preloader -->
    <div id="preloder" style="display: none;">
        <div class="loader"></div>
    </div>

    <?php include 'partials/header.php'; ?>

    <!-- Breadcrumb Section Begin -->
    <section class="breadcrumb-section set-bg" data-setbg="img/Frame3.png">
        <div class="container text-center">
            <h2 style="color:black">Shop</h2>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Product Section Begin -->
    <section class="product spad">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 col-md-5">
                    <div class="sidebar">
                        <!-- Categories -->
                        <div class="sidebar__item">
                            <h4>Department</h4>
                            <ul>
                                <?php
                                if ($result_categories->num_rows > 0) {
                                    while ($row = $result_categories->fetch_assoc()) {
                                        $cat_id = $row['id'];
                                        $cat_name = $row['name'];
                                        $is_active = ($category_id == $cat_id) ? 'active' : '';
                                        echo "<li class='$is_active'><a href='?category_id=$cat_id'>$cat_name</a></li>";
                                    }
                                } else {
                                    echo "<li>No categories available</li>";
                                }
                                ?>
                            </ul>
                        </div>

                        <!-- Latest Products -->
                        <div class="sidebar__item">
                            <div class="latest-product__text">
                                <h4>Latest Products</h4>
                                <div class="latest-product__slider owl-carousel">
                                    <div class="latest-prdouct__slider__item">
                                        <?php foreach ($latestProducts as $product): ?>
                                            <a href="shop-details.php?id=<?php echo $product['id']; ?>" class="latest-product__item">
                                                <div class="latest-product__item__pic">
                                                    <img src="../uploads/<?php echo $product['image']; ?>" alt="">
                                                </div>
                                                <div class="latest-product__item__text">
                                                    <h6><?php echo $product['name']; ?></h6>
                                                    <span>৳<?php echo number_format($product['price'], 2); ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="col-lg-9 col-md-7">
                    <div class="filter__item">
                        <div class="row">
                            <div class="col-lg-4 col-md-5">
                                <div class="filter__sort">
                                    <span>Sort By</span>
                                    <select onchange="location = this.value;">
                                        <option value="?sort=all<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>" <?php echo ($sort_option == 'all') ? 'selected' : ''; ?>>All Products</option>
                                        <option value="?sort=latest<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>" <?php echo ($sort_option == 'latest') ? 'selected' : ''; ?>>Latest</option>
                                        <option value="?sort=price_high_to_low<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>" <?php echo ($sort_option == 'price_high_to_low') ? 'selected' : ''; ?>>Price High to Low</option>
                                        <option value="?sort=price_low_to_high<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>" <?php echo ($sort_option == 'price_low_to_high') ? 'selected' : ''; ?>>Price Low to High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php if (count($latestProducts) > 0): ?>
                            <?php foreach ($latestProducts as $product): ?>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="product__item">
                                        <a href="shop-details.php?id=<?php echo $product['id']; ?>">
                                            <div class="product__item__pic set-bg" data-setbg="../uploads/<?php echo $product['image']; ?>"></div>
                                        </a>
                                        <div class="product__item__text">
                                            <h6><a href="shop-details.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></h6>
                                            <h5>৳<?php echo number_format($product['price'], 2); ?></h5>
                                            <button class="quick-add-to-cart" data-product-id="<?php echo $product['id']; ?>" data-product-name="<?php echo htmlspecialchars($product['name']); ?>">
                                                <i class="fa fa-shopping-cart"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <p>No products found in this category.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Product Section End -->

    <!-- Quick Add to Cart CSS + JS -->
    <style>
        .quick-add-to-cart {
            background: #7fad39;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }
        .quick-add-to-cart:hover { background: #6b9a2f; }
        .quick-add-to-cart:disabled { background: #ccc; cursor: not-allowed; }
        .quick-add-to-cart.success { background: #28a745; }
        .quick-add-to-cart.error { background: #dc3545; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const addToCartButtons = document.querySelectorAll('.quick-add-to-cart');
        
        addToCartButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const productName = this.getAttribute('data-product-name');
                const originalText = this.innerHTML;

                this.disabled = true;
                this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Adding...';

                fetch('quick_add_to_cart.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'product_id=' + productId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.innerHTML = '<i class="fa fa-check"></i> Added!';
                        this.classList.add('success');
                        updateCartCount(data.cart_count);
                        showNotification('Product added successfully!', 'success');
                    } else {
                        this.innerHTML = '<i class="fa fa-times"></i> Error!';
                        this.classList.add('error');
                        showNotification(data.message || 'Failed to add product', 'error');
                    }
                })
                .catch(() => {
                    this.innerHTML = '<i class="fa fa-times"></i> Error!';
                    this.classList.add('error');
                    showNotification('Network error', 'error');
                })
                .finally(() => {
                    setTimeout(() => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        this.classList.remove('success','error');
                    }, 2000);
                });
            });
        });

        function updateCartCount(count) {
            document.querySelectorAll('.cart-count').forEach(el => el.textContent = count);
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed; top: 20px; right: 20px;
                padding: 15px 20px; border-radius: 4px;
                color: white; font-weight: bold;
                z-index: 10000; animation: slideIn 0.3s ease;
                ${type === 'success' ? 'background: #28a745;' : 'background: #dc3545;'}
            `;
            if (!document.querySelector('#notification-styles')) {
                const style = document.createElement('style');
                style.id = 'notification-styles';
                style.textContent = `@keyframes slideIn {from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}`;
                document.head.appendChild(style);
            }
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
    });

    // Ensure preloader hides
    window.addEventListener("load", function() {
        document.getElementById("preloder").style.display = "none";
    });
    </script>

    <!-- Footer -->
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
                        <p>We welcome your feedback on our service or products.</p>
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
                <div class='col-lg-12 text-center'>
                    <p>&copy; <script>document.write(new Date().getFullYear())</script> All rights reserved</p>
                </div>
            </div>
        </div>
    </footer>

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
        // Hide preloader when page is fully loaded
        window.addEventListener('load', function() {
            document.getElementById('preloder').style.display = 'none';
            console.log('Page fully loaded');
        });
        
        // Fallback: Hide preloader after 5 seconds in case page load event doesn't fire
        setTimeout(function() {
            var preloader = document.getElementById('preloder');
            if (preloader) {
                preloader.style.display = 'none';
                console.log('Preloader hidden by timeout');
            }
        }, 5000);
    </script>
</body>
</html>

<?php
// Close database connection at the very end
if (isset($conn) && $conn !== null) {
    $conn->close();
    error_log("Database connection closed");
}
?>
