<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['admin'])) {
    header("location: login.php");
    exit;
}

include '../db.php';

// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $query = "SELECT image FROM products WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    unlink("../uploads/" . $row['image']); // Delete the image file

    $deleteQuery = "DELETE FROM products WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Product Deleted'); window.location.href='productTable.php';</script>";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

// Handle Update Request
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $productCode = $_POST['productCode'];
    $weight = $_POST['weight'];
    $careNote = $_POST['careNote'];

    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $query = "SELECT image FROM products WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        unlink("../uploads/" . $row['image']); // Delete old image

        $updateQuery = "UPDATE products SET name='$name', description='$description', price='$price', product_code='$productCode', weight='$weight', care_note='$careNote', image='$image' WHERE id=$id";
    } else {
        $updateQuery = "UPDATE products SET name='$name', description='$description', price='$price', product_code='$productCode', weight='$weight', care_note='$careNote' WHERE id=$id";
    }

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Product Updated'); window.location.href='productTable.php';</script>";
    } else {
        echo "Error updating product: " . mysqli_error($conn);
    }
}

// Fetch Products
$query = "SELECT * FROM products";
$result = mysqli_query($conn, $query);
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
                <h2 class="text-center">Product Management</h2>

                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Product Code</th>
                            <th>Weight</th>
                            <th>Care Note</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['price']; ?></td>
                                <td><?php echo $row['product_code']; ?></td>
                                <td><?php echo $row['weight']; ?></td>
                                <td><?php echo $row['care_note']; ?></td>
                                <td><img src="../uploads/<?php echo $row['image']; ?>" alt="Product Image" width="50"
                                        height="50"></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                                    <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Product</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                                <div class="mb-3">
                                                    <label class="form-label">Product Name:</label>
                                                    <input type="text" class="form-control" name="name"
                                                        value="<?php echo $row['name']; ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description:</label>
                                                    <textarea class="form-control" name="description"
                                                        required><?php echo $row['description']; ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Price:</label>
                                                    <input type="number" class="form-control" name="price"
                                                        value="<?php echo $row['price']; ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Product Code:</label>
                                                    <input type="text" class="form-control" name="productCode"
                                                        value="<?php echo $row['product_code']; ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Weight:</label>
                                                    <input type="number" class="form-control" name="weight"
                                                        value="<?php echo $row['weight']; ?>" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Care Note:</label>
                                                    <textarea class="form-control" name="careNote"
                                                        required><?php echo $row['care_note']; ?></textarea>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Current Image:</label>
                                                    <img src="../uploads/<?php echo $row['image']; ?>" width="100"
                                                        class="d-block mb-2">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Upload New Image:</label>
                                                    <input type="file" class="form-control" name="image">
                                                </div>

                                                <button type="submit" name="update" class="btn btn-success">Update</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kaiadmin JS -->
        <script src="assets/js/kaiadmin.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </div>
</body>

</html>