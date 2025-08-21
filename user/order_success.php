<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zxx">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Ogani Template">
    <meta name="keywords" content="Ogani, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Rosella - Order Success</title>
    <link rel="icon" href="img/logo1.png" type="image/x-icon">
    <!-- Css Styles -->
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/elegant-icons.css" type="text/css">
    <link rel="stylesheet" href="css/nice-select.css" type="text/css">
    <link rel="stylesheet" href="css/jquery-ui.min.css" type="text/css">
    <link rel="stylesheet" href="css/owl.carousel.min.css" type="text/css">
    <link rel="stylesheet" href="css/slicknav.min.css" type="text/css">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <style>
        .success-wrap{max-width:680px;margin:60px auto;text-align:center}
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
                        <h2>Order Success</h2>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="checkout spad">
        <div class="container success-wrap">
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for your order. We will process it shortly.</p>
            <?php if (isset($_SESSION['account_created'])): ?>
                <div class="alert alert-info" style="text-align:left;">
                    <strong>Account Created:</strong> We created an account for you.<br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['account_created']['email']); ?><br>
                    <strong>Temporary Password:</strong> <?php echo htmlspecialchars($_SESSION['account_created']['password']); ?><br>
                    Please update your password from your profile later.
                </div>
                <?php unset($_SESSION['account_created']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my_orders.php" class="site-btn" style="margin-right:10px;">View My Orders</a>
            <?php endif; ?>
            <a href="index.php" class="site-btn" style="background:#6c757d;">Continue Shopping</a>
        </div>
    </section>

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