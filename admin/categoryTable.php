<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if(!isset($_SESSION['admin'])){
    header("location: login.php");
    exit;
  }
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db.php';
// Handle Delete Request
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $query = "SELECT image FROM categories WHERE id = $id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    unlink("../uploads/" . $row['image']); // Delete the image file

    $deleteQuery = "DELETE FROM categories WHERE id = $id";
    if (mysqli_query($conn, $deleteQuery)) {
        echo "<script>alert('Category Deleted'); window.location.href='categoryTable.php';</script>";
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}

// Handle Update Request
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];

    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $query = "SELECT image FROM categories WHERE id = $id";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);
        unlink("../uploads/" . $row['image']); // Delete old image

        $updateQuery = "UPDATE categories SET name='$name', image='$image' WHERE id=$id";
    } else {
        $updateQuery = "UPDATE categories SET name='$name' WHERE id=$id";
    }

    if (mysqli_query($conn, $updateQuery)) {
        echo "<script>alert('Category Updated'); window.location.href='categoryTable.php';</script>";
    } else {
        echo "Error updating category: " . mysqli_error($conn);
    }
}

// Fetch Categories
$query = "SELECT * FROM categories";
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
                    <img  src="assets/img/logo2.png" alt="navbar brand" class="navbar-brand" height="80" width="100" />
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
          <i class="fas fa-home"></i>
          <p>Dashboard</p>
        </a>
      </li>
      <li class="nav-item active">
        <a href="./category.php" class="collapsed" aria-expanded="false">
          <i class="fas fa-tags"></i>
          <p>Add Category</p>
        </a>
      </li>
      <li class="nav-item active">
        <a href="./categoryTable.php" class="collapsed" aria-expanded="false">
          <i class="fas fa-tags"></i>
          <p>All Category</p>
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
                        <img  src="assets/img/logo2.png" alt="navbar brand" class="navbar-brand" height="80" width="100" />
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
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <button type="submit" class="btn btn-search pe-1">
                                        <i class="fa fa-search search-icon"></i>
                                    </button>
                                </div>
                                <input type="text" placeholder="Search ..." class="form-control" />
                            </div>
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

            <div class="container mt-16">
    <h2 class="text-center">Category Management</h2>

    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><img src="../uploads/<?php echo $row['image']; ?>" alt="Category Image" width="50" height="50"></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">Edit</button>
                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form action="" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Category Name:</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo $row['name']; ?>" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Current Image:</label>
                                        <img src="../uploads/<?php echo $row['image']; ?>" width="100" class="d-block mb-2">
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

</body>

</html>