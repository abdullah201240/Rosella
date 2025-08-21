<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';

// Fetch categories
$sql_categories = "SELECT * FROM categories"; // Adjust the table and column names as per your database
$result_categories = $conn->query($sql_categories);

// Determine the sort option
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'all';

// Determine the selected category
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Fetch products based on the sort option and category
$sql_products = "SELECT * FROM products";

// Add category filter if a category is selected
if ($category_id) {
    $sql_products .= " WHERE category_id = $category_id";
}

// Add sorting based on the selected option
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
    case 'all':
    default:
        // No additional sorting for "All Products"
        break;
}

// Add LIMIT 20 only if the sort option is not "all"
if ($sort_option !== 'all') {
    $sql_products .= " LIMIT 20"; // Limit to 20 products for other sort options
}

$result_products = $conn->query($sql_products);

$latestProducts = [];
while ($row = $result_products->fetch_assoc()) {
    $latestProducts[] = $row;
}

$conn->close();
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
                        <h2 style="color:black">Shop</h2>

                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Breadcrumb Section End -->

    <!-- Product Section Begin -->
    <section class="product spad">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-5">
                    <div class="sidebar">
                        <div class="sidebar__item">
                            <h4>Department</h4>
                            <ul>
                                <?php
                                if ($result_categories->num_rows > 0) {
                                    // Output data of each row
                                    while ($row = $result_categories->fetch_assoc()) {
                                        $category_id = $row['id'];
                                        $category_name = $row['name'];
                                        $is_active = (isset($_GET['category_id']) && $_GET['category_id'] == $category_id) ? 'active' : '';
                                        echo "<li class='$is_active'><a href='?category_id=$category_id'>$category_name</a></li>";
                                    }
                                } else {
                                    echo "<li>No categories available</li>";
                                }
                                ?>
                            </ul>
                        </div>


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
                                                    <span>৳<?php echo $product['price']; ?></span>
                                                </div>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-9 col-md-7">

                    <div class="filter__item">
                        <div class="row">
                            <div class="col-lg-4 col-md-5">
                                <div class="filter__sort">
                                    <span>Sort By</span>
                                    <select onchange="location = this.value;">
                                        <option
                                            value="?sort=all<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>"
                                            <?php echo ($sort_option == 'all') ? 'selected' : ''; ?>>All Products</option>
                                        <option
                                            value="?sort=latest<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>"
                                            <?php echo ($sort_option == 'latest') ? 'selected' : ''; ?>>Latest</option>
                                        <option
                                            value="?sort=price_high_to_low<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>"
                                            <?php echo ($sort_option == 'price_high_to_low') ? 'selected' : ''; ?>>Price
                                            High to Low</option>
                                        <option
                                            value="?sort=price_low_to_high<?php echo $category_id ? '&category_id=' . $category_id : ''; ?>"
                                            <?php echo ($sort_option == 'price_low_to_high') ? 'selected' : ''; ?>>Price
                                            Low to High</option>
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
                                        <div class="product__item__pic set-bg"
                                            data-setbg="../uploads/<?php echo $product['image']; ?>">
                                            
                                        </div>
                                        </a>
                                        <div class="product__item__text">
                                            <h6><a href="shop-details.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></h6>
                                            <h5>৳<?php echo $product['price']; ?></h5>
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



</body>

</html>