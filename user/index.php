<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../db.php';

// SQL query to fetch categories
$sql = 'SELECT * FROM categories';
$result = $conn->query($sql);

?>
<?php

// SQL query to fetch categories
$sql_categories = 'SELECT * FROM categories';
$result_categories = $conn->query($sql_categories);

// SQL query to fetch products with category names
$sql_products = '
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC 
    LIMIT 10';
$result_products = $conn->query($sql_products);

// Function to sanitize category names
function sanitizeCategoryName($name)
{
    // Replace spaces with hyphens and convert to lowercase
    return strtolower(str_replace(' ', '-', $name));
}
?>

<!DOCTYPE html>
<html lang='zxx'>

<head>
    <meta charset='UTF-8'>
    <meta name='description' content='Ogani Template'>
    <meta name='keywords' content='Ogani, unica, creative, html'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv='X-UA-Compatible' content='ie=edge'>
    <title>Rosella</title>
    <link rel='icon' href='img/logo1.png' type='image/x-icon'>

    <!-- Google Font -->
    <link href='https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;900&display=swap' rel='stylesheet'>

    <!-- Css Styles -->
    <link rel='stylesheet' href='css/bootstrap.min.css' type='text/css'>
    <link rel='stylesheet' href='css/font-awesome.min.css' type='text/css'>
    <link rel='stylesheet' href='css/elegant-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/nice-select.css' type='text/css'>
    <link rel='stylesheet' href='css/jquery-ui.min.css' type='text/css'>
    <link rel='stylesheet' href='css/owl.carousel.min.css' type='text/css'>
    <link rel='stylesheet' href='css/slicknav.min.css' type='text/css'>
    <link rel='stylesheet' href='css/style.css' type='text/css'>
</head>

<body>
    <!-- Page Preloder -->
    <div id='preloder'>
        <div class='loader'></div>
    </div>

    <!-- Humberger Begin -->
    <div class='humberger__menu__overlay'></div>
    <div class='humberger__menu__wrapper'>
        <div class='humberger__menu__logo'>
            <a href='/index.php'><img src='img/logo1.png' alt=''></a>
        </div>

        <nav class='humberger__menu__nav mobile-menu'>
            <ul>
                <li class='active'><a href='./index.php'>Home</a></li>
                <li><a href='./shop-grid.php'>Shop</a></li>

                <li><a href='./shoping-cart.php'>Shoping Cart</a></li>

                <li><a href='./contact.php'>Contact</a></li>
            </ul>
        </nav>
        <div id='mobile-menu-wrap'></div>

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

    <!-- Hero Section Begin -->
    <section class='hero'>
        <div class='container'>
            <div class='row'>

                <div class='col-lg-12'>

                    <div class='hero__item set-bg' data-setbg='img/hero/Frame2.png'>
                        <div class='hero__text'>
                            <h4>FASHION ELEGANCE</h4>
                            <h2>Dresses | Shoes | Bags <br />
                            </h2>
                            <h4>100% Stylish & Premium Quality</h4>

                            <p class='mt-2'>Free Pickup and Delivery Available</p>
                            <a href='./shop-grid.php' class='primary-btn'>SHOP NOW</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero Section End -->

    <!-- Categories Section Begin -->
    <section class='categories'>
        <div class='container'>
            <div class='row'>
                <div class='categories__slider owl-carousel'>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class='col-lg-3'>
            <a href='shop-grid.php?category_id=<?php echo $row['id']; ?>'>

                <div class='categories__item set-bg' data-setbg="../uploads/<?php echo $row['image']; ?>">
                <div style="display: flex; justify-content: center; align-items: center; height: 100vh; width: 100vw; margin: 0; padding: 0;">

                <h5 style="background-color: white; width: 80%; height: 40px; text-align: center; display: flex; justify-content: center; align-items: center;">
                        
                            <?php echo $row['name']; ?>
                            
                    </h5>
                    </div>

                </div>
                </a>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No categories found.</p>
    <?php endif; ?>
</div>
            </div>
        </div>
    </section>
    <!-- Categories Section End -->

    <!-- Featured Section Begin -->
    <section class='featured spad'>
        <div class='container'>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='section-title'>
                        <h2>Featured Product</h2>
                    </div>
                    <!-- Filter Controls -->
                    <div class='featured__controls'>
                        <ul>
                            <li class='active' data-filter='*'>All</li>
                            <?php if ($result_categories->num_rows > 0): ?>
                                <?php while ($category = $result_categories->fetch_assoc()): ?>
                                    <li data-filter='.<?php echo sanitizeCategoryName($category['name']); ?>'>
                                        <?php echo $category['name']; ?>
                                    </li>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- Products Display -->
            <div class='row featured__filter'>
                <?php if ($result_products->num_rows > 0): ?>
                    <?php while ($product = $result_products->fetch_assoc()): ?>
                        <div
                            class='col-lg-3 col-md-4 col-sm-6 mix <?php echo sanitizeCategoryName($product['category_name']); ?>'>
                            <div class='featured__item'>
                                <div class='featured__item__pic set-bg'
                                    data-setbg='../uploads/<?php echo $product['image']; ?>'>
                                    <!-- Make the entire image area clickable -->
                                    <a href='shop-details.php?id=<?php echo $product['id']; ?>'
                                        style='display: block; height: 100%; width: 100%; position: absolute; top: 0; left: 0; z-index: 1;'></a>

                                </div>
                                <div class='featured__item__text'>
                                    <h6><a
                                            href='shop-details.php?id=<?php echo $product['id']; ?>'><?php echo $product['name']; ?></a>
                                    </h6>
                                    <h5>৳<?php echo $product['price']; ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Featured Section End -->

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
    <script src='js/jquery-3.3.1.min.js'></script>
    <script src='js/bootstrap.min.js'></script>
    <script src='js/jquery.nice-select.min.js'></script>
    <script src='js/jquery-ui.min.js'></script>
    <script src='js/jquery.slicknav.js'></script>
    <script src='js/mixitup.min.js'></script>
    <script src='js/owl.carousel.min.js'></script>
    <script src='js/main.js'></script>
    <!-- JavaScript for filtering -->
    <script src='js/jquery-3.3.1.min.js'></script>
    <script src='js/mixitup.min.js'></script>
    <script>
        $(document).ready(function () {
            var mixer = mixitup('.featured__filter');
        });
    </script>

</body>

</html>