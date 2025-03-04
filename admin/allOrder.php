<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['admin'])) {
    header("location: login.php");
    exit;
}

include '../db.php';

// Fetch all orders from the database
$sql = "SELECT * FROM orders";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>Rosella</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="assets/img/logo1.png" type="image/x-icon" />

    <!-- Fonts and icons -->
    <script src="assets/js/plugin/webfont/webfont.min.js"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["assets/css/fonts.min.css"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/plugins.min.css" />
    <link rel="stylesheet" href="assets/css/kaiadmin.min.css" />

    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link rel="stylesheet" href="assets/css/demo.css" />

    <style>
        /* Custom CSS for scrollable table */
        .table-container {
            width: 100%;
            overflow: auto;
            max-height: 600px;
            /* Adjust height as needed */
            border: 1px solid #dee2e6;
        }

        .table {
            min-width: 1200px;
            /* Ensure the table has a minimum width */
            width: 100%;
        }

        .table th,
        .table td {
            white-space: nowrap;
            /* Prevent text wrapping */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" data-background-color="dark">
            <div class="sidebar-logo">
                <!-- Logo Header -->
                <div class="logo-header" data-background-color="dark">
                    <a href="index.php" class="logo">
                        <img src="assets/img/logo2.png" alt="navbar brand" class="navbar-brand" height="80"
                            width="100" />
                    </a>
                    <div class="nav-toggle">
                        <button class="btn btn-toggle toggle-sidebar">
                            <i class="gg-menu-right"></i>
                        </button>
                        <button class="btn btn-toggle sidenav-toggler">
                            <i class="gg-menu-left"></i>
                        </button>
                    </div>
                    <button class="topbar-toggler more">
                        <i class="gg-more-vertical-alt"></i>
                    </button>
                </div>
                <!-- End Logo Header -->
            </div>
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <ul class="nav nav-secondary">
                        <li class="nav-item active">
                            <a href="./index.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./category.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-plus-circle"></i>
                                <p>Add Category</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./categoryTable.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-th-list"></i>
                                <p>All Categories</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./addProducts.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-plus-square"></i>
                                <p>Add Product</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./productTable.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-boxes"></i>
                                <p>All Products</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./allOrder.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-shopping-cart"></i>
                                <p>All Orders</p>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a href="./contactTable.php" class="collapsed" aria-expanded="false">
                                <i class="fas fa-envelope"></i>
                                <p>Contact Messages</p>
                            </a>
                        </li>
                        <li class="nav-item active">
        <a href="./logout.php" class="collapsed" aria-expanded="false">
            <i class="fas fa-sign-out-alt"></i>
            <p>Logout</p>
        </a>
    </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- End Sidebar -->

        <div class="main-panel">
            <div class="main-header">
                <div class="main-header-logo">
                    <!-- Logo Header -->
                    <div class="logo-header" data-background-color="dark">
                        <a href="index.php" class="logo">
                            <img src="assets/img/logo2.png" alt="navbar brand" class="navbar-brand" height="80"
                                width="100" />
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar">
                                <i class="gg-menu-right"></i>
                            </button>
                            <button class="btn btn-toggle sidenav-toggler">
                                <i class="gg-menu-left"></i>
                            </button>
                        </div>
                        <button class="topbar-toggler more">
                            <i class="gg-more-vertical-alt"></i>
                        </button>
                    </div>
                    <!-- End Logo Header -->
                </div>
                <!-- Navbar Header -->
                <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                    <div class="container-fluid">
                        <nav
                            class="navbar navbar-header-left navbar-expand-lg navbar-form nav-search p-0 d-none d-lg-flex">

                        </nav>

                        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                            <li class="nav-item topbar-icon dropdown hidden-caret d-flex d-lg-none">
                                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                                    aria-expanded="false" aria-haspopup="true">
                                    <i class="fa fa-search"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-search animated fadeIn">
                                    <form class="navbar-left navbar-form nav-search">
                                        <div class="input-group">
                                            <input type="text" placeholder="Search ..." class="form-control" />
                                        </div>
                                    </form>
                                </ul>
                            </li>

                            <li class="nav-item topbar-user dropdown hidden-caret">
                                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#"
                                    aria-expanded="false">
                                    
                                    <span class="profile-username">
                                        <span class="op-7">Hi,</span>
                                        <span class="fw-bold"><?php echo $_SESSION['admin_name'] ?></php></span>
                                        </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- End Navbar -->
            </div>

            <div class="container mt-16">
                <h2 class="text-center">Order Management</h2>

                <!-- Scrollable Table Container -->
                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Country</th>
                                <th>Address</th>
                                <th>City</th>
                                <th>State</th>
                                <th>Postcode</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Order Notes</th>
                                <th>Total Amount</th>
                                <th>Products</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $row['id'] . "</td>
                                            <td>" . htmlspecialchars($row['first_name']) . "</td>
                                            <td>" . htmlspecialchars($row['last_name']) . "</td>
                                            <td>" . htmlspecialchars($row['country']) . "</td>
                                            <td>" . htmlspecialchars($row['address']) . " " . htmlspecialchars($row['address2']) . "</td>
                                            <td>" . htmlspecialchars($row['city']) . "</td>
                                            <td>" . htmlspecialchars($row['state']) . "</td>
                                            <td>" . htmlspecialchars($row['postcode']) . "</td>
                                            <td>" . htmlspecialchars($row['phone']) . "</td>
                                            <td>" . htmlspecialchars($row['email']) . "</td>
                                            <td>" . htmlspecialchars($row['order_notes']) . "</td>
                                            <td>" . number_format($row['total_amount'], 2) . "</td>
                                            <td>";

                                    // Decode the JSON string to an array
                                    $products = json_decode($row['products'], true);

                                    // Check if decoding was successful
                                    if (is_array($products)) {
                                        echo "<ul>";
                                        foreach ($products as $product) {
                                            echo "<li>" . htmlspecialchars($product['product_name']) . " - Quantity: " . htmlspecialchars($product['quantity']) . " - Price: " . number_format($product['product_price'], 2) . "</li>";
                                        }
                                        echo "</ul>";
                                    } else {
                                        echo "Invalid product data.";
                                    }

                                    echo "</td>
                                            <td>
                                                <select class='form-select status-dropdown' data-order-id='" . $row['id'] . "'>
                                                    <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                                                    <option value='handover' " . ($row['status'] == 'handover' ? 'selected' : '') . ">Handover to Delivery Man</option>
                                                    <option value='on_the_way' " . ($row['status'] == 'on_the_way' ? 'selected' : '') . ">On the Way</option>
                                                    <option value='completed' " . ($row['status'] == 'completed' ? 'selected' : '') . ">Completed</option>
                                                    <option value='canceled' " . ($row['status'] == 'canceled' ? 'selected' : '') . ">Canceled</option>

                                                </select>
                                            </td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='14'>No orders found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kaiadmin JS -->
        <script src="assets/js/kaiadmin.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Add jQuery for AJAX -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            $(document).ready(function () {
                $('.status-dropdown').on('change', function () {
                    var orderId = $(this).data('order-id');
                    var newStatus = $(this).val();

                    $.ajax({
                        url: 'update_order_status.php',
                        type: 'POST',
                        data: {
                            order_id: orderId,
                            status: newStatus
                        },
                        success: function (response) {
                            if (response == 'success') {
                                alert('Status updated successfully!');
                            } else {
                                alert('Failed to update status.');
                            }
                        },
                        error: function () {
                            alert('An error occurred while updating the status.');
                        }
                    });
                });
            });
        </script>
    </div>
</body>

</html>