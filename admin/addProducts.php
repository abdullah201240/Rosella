<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['admin'])) {
    header("location: login.php");
    exit;
}

include '../db.php';  // Ensure this file properly connects to the database

// Fetch categories from the database
$categories = [];
$categoryQuery = $conn->query("SELECT id, name FROM categories");
if ($categoryQuery) {
    while ($row = $categoryQuery->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get product details
    $productName = $_POST['name'];
    $productDescription = $_POST['description'];
    $productPrice = $_POST['price'];
    $productCode = $_POST['productCode'];
    $productWeight = $_POST['weight'];
    $careNote = $_POST['careNote'];
    $categoryId = $_POST['category']; // Get the selected category ID

    // Handle file upload
    $targetDir = "../uploads/";  // Ensure this directory exists
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);  // Create if not exists
    }

    $image = $_FILES['file'];
    $imageFilePath = null;

    // Validate and process image upload
    if (!empty($image['name'])) {
        $imageName = basename($image['name']);
        $imageFileType = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $newFileName = uniqid() . '.' . $imageFileType;  // Unique name
        $targetFilePath = $targetDir . $newFileName;

        // Allowed file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($imageFileType, $allowedTypes)) {
            die("Error: Only JPG, JPEG, PNG, webp and GIF files are allowed.");
        }

        // Move uploaded file
        if (move_uploaded_file($image['tmp_name'], $targetFilePath)) {
            $imageFilePath = $newFileName; // Save filename in DB
        } else {
            die("Error: Failed to upload image.");
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, product_code, weight, care_note, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssssi", $productName, $productDescription, $productPrice, $imageFilePath, $productCode, $productWeight, $careNote, $categoryId);

    if ($stmt->execute()) {
        echo "New product added successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close resources
    $stmt->close();
    $conn->close();
}
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
                                    <div class="avatar-sm">
                                        <img src="assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle" />
                                    </div>
                                    <span class="profile-username">
                                        <span class="op-7">Hi,</span>
                                        <span class="fw-bold">Amrin</span>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- End Navbar -->
            </div>

            <div class="container">
                <div class="page-inner">
                    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                        <div>
                            <h3 class="fw-bold">Product</h3>
                        </div>
                    </div>

                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-md-8">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-light">
                                        <h5 class="card-title mb-0">Product Details</h5>
                                    </div>
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="name">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter Product Name" aria-label="Product Name" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description"
                                                placeholder="Enter Product Description" aria-label="Product Description"
                                                required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label for="price">Price</label>
                                            <input type="number" class="form-control" id="price" name="price"
                                                placeholder="Enter Product Price" aria-label="Product Price" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="productCode">Product Code</label>
                                            <input type="text" class="form-control" id="productCode" name="productCode"
                                                placeholder="Enter Product Code" aria-label="Product Code" required />
                                        </div>
                                        <div class="form-group">
                                            <label for="weight">Product Weight</label>
                                            <input type="number" class="form-control" id="weight" name="weight"
                                                placeholder="Enter Product Weight" aria-label="Product Weight"
                                                required />
                                        </div>
                                        <div class="form-group">
                                            <label for="category">Category</label>
                                            <select class="form-control" id="category" name="category" required>
                                                <option value="">Select a category</option>
                                                <?php foreach ($categories as $category): ?>
                                                    <option value="<?php echo $category['id']; ?>">
                                                        <?php echo htmlspecialchars($category['name']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="careNote">Care Note</label>
                                            <textarea class="form-control" id="careNote" name="careNote"
                                                placeholder="Enter Care Note" aria-label="Care Note"
                                                required></textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="exampleFormControlFile1">Product Image</label>
                                            <input type="file" class="form-control-file" id="exampleFormControlFile1"
                                                name="file" aria-label="File input" />
                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-success mr-2">Submit</button>
                                            <button type="reset" class="btn btn-danger">Cancel</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kaiadmin JS -->
        <script src="assets/js/kaiadmin.min.js"></script>
</body>

</html>