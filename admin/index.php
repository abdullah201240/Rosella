<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['admin'])) {
    header("location: login.php");
    exit;
}

include("../db.php");

// Fetch dashboard statistics
$stats = [];

// Total Sales and Revenue
$result = mysqli_query($conn, "SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
    SUM(total_amount) as total_revenue,
    SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_revenue,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT email) as total_customers
    FROM orders");
$stats = mysqli_fetch_assoc($result);

// Revenue by status
$revenueByStatus = [];
$result = mysqli_query($conn, "SELECT 
    status, 
    COUNT(*) as order_count, 
    SUM(total_amount) as total_amount 
    FROM orders 
    GROUP BY status");
while ($row = mysqli_fetch_assoc($result)) {
    $revenueByStatus[] = $row;
}

// Latest orders
$latestOrdersQuery = "SELECT o.*, 
    (SELECT COUNT(*) FROM orders o2 WHERE o2.email = o.email) as customer_order_count
    FROM orders o 
    ORDER BY order_date DESC LIMIT 5";
$latestOrdersResult = mysqli_query($conn, $latestOrdersQuery);

// Recent transactions
$transactionsQuery = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 10";
$transactionsResult = mysqli_query($conn, $transactionsQuery);

// Get monthly revenue for the last 6 months
$monthlyRevenue = [];
$result = mysqli_query($conn, "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') as month,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as revenue,
        COUNT(CASE WHEN status = 'completed' THEN id END) as orders
    FROM orders 
    WHERE order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY month ASC
");
while ($row = mysqli_fetch_assoc($result)) {
    $monthlyRevenue[] = $row;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>Rosella - Admin Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #6c63ff;
      --secondary-color: #f8f9fa;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --danger-color: #dc3545;
      --dark-color: #343a40;
    }
    body {
      background-color: #f5f6fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .sidebar {
      min-height: 100vh;
      background: var(--dark-color);
      color: white;
    }
    .card {
      border: none;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
      transition: transform 0.3s ease;
      margin-bottom: 20px;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card-icon {
      font-size: 2rem;
      opacity: 0.7;
    }
    .stat-card {
      border-left: 4px solid var(--primary-color);
    }
    .stat-card.primary { border-left-color: var(--primary-color); }
    .stat-card.success { border-left-color: var(--success-color); }
    .stat-card.warning { border-left-color: var(--warning-color); }
    .stat-card.danger { border-left-color: var(--danger-color); }
    .revenue-chart {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
    }
    .table-responsive {
      background: white;
      border-radius: 10px;
      padding: 20px;
    }
    .status-badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-completed { background-color: #d4edda; color: #155724; }
    .status-canceled { background-color: #f8d7da; color: #721c24; }
  </style>
  <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
  <link rel="icon" href="assets/img/logo1.png" type="image/x-icon" />
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <img src="assets/img/logo2.png" alt="Rosella" class="img-fluid" style="max-height: 60px;">
          </div>
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link active text-white" href="index.php">
                <i class="fas fa-tachometer-alt me-2"></i>
                Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="category.php">
                <i class="fas fa-plus-circle me-2"></i>
                Add Category
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="categoryTable.php">
                <i class="fas fa-th-list me-2"></i>
                All Categories
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="addProducts.php">
                <i class="fas fa-plus-square me-2"></i>
                Add Product
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="productTable.php">
                <i class="fas fa-boxes me-2"></i>
                All Products
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="allOrder.php">
                <i class="fas fa-shopping-cart me-2"></i>
                All Orders
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="contactTable.php">
                <i class="fas fa-envelope me-2"></i>
                Contact Messages
              </a>
            </li>
            <li class="nav-item mt-4">
              <a class="nav-link text-danger" href="logout.php">
                <i class="fas fa-sign-out-alt me-2"></i>
                Logout
              </a>
            </li>
          </ul>
        </div>
      </div>

      <!-- Main Content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Dashboard Overview</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            </div>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar-alt me-1"></i>
                This month
              </button>
              <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                <li><a class="dropdown-item" href="#">Today</a></li>
                <li><a class="dropdown-item" href="#">This Week</a></li>
                <li><a class="dropdown-item" href="#">This Month</a></li>
                <li><a class="dropdown-item" href="#">This Year</a></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card primary h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Total Revenue</h6>
                    <h3 class="mb-0">৳<?php echo number_format($stats['completed_revenue'] ?? 0, 2); ?></h3>
                    <p class="text-success mb-0"><small>+<?php echo $stats['completed_orders'] ?? 0; ?> orders</small></p>
                  </div>
                  <div class="card-icon bg-primary bg-opacity-10 p-3 rounded">
                    <i class="fas fa-taka-sign text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card success h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Total Orders</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
                    <p class="text-muted mb-0"><small>Completed: <?php echo $stats['completed_orders'] ?? 0; ?></small></p>
                  </div>
                  <div class="card-icon bg-success bg-opacity-10 p-3 rounded">
                    <i class="fas fa-shopping-cart text-success"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card warning h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Avg. Order Value</h6>
                    <h3 class="mb-0">৳<?php echo number_format($stats['avg_order_value'] ?? 0, 2); ?></h3>
                    <p class="text-muted mb-0"><small>From <?php echo $stats['total_customers'] ?? 0; ?> customers</small></p>
                  </div>
                  <div class="card-icon bg-warning bg-opacity-10 p-3 rounded">
                    <i class="fas fa-chart-line text-warning"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card danger h-100">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <h6 class="text-muted mb-1">Pending Orders</h6>
                    <h3 class="mb-0"><?php echo $stats['pending_orders'] ?? 0; ?></h3>
                    <p class="text-danger mb-0"><small>Needs attention</small></p>
                  </div>
                  <div class="card-icon bg-danger bg-opacity-10 p-3 rounded">
                    <i class="fas fa-clock text-danger"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
          <!-- Revenue Chart -->
          <div class="col-lg-8 mb-4">
            <div class="card h-100">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">Revenue Overview</h6>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Last 6 Months
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="revenueDropdown">
                    <li><a class="dropdown-item" href="#">This Year</a></li>
                    <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                  </ul>
                </div>
              </div>
              <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
              </div>
            </div>
          </div>

          <!-- Order Status Chart -->
          <div class="col-lg-4 mb-4">
            <div class="card h-100">
              <div class="card-header">
                <h6 class="card-title mb-0">Orders by Status</h6>
              </div>
              <div class="card-body d-flex justify-content-center">
                <div style="width: 250px; height: 250px;">
                  <canvas id="orderStatusChart"></canvas>
                </div>
              </div>
              <div class="card-footer bg-transparent">
                <div class="row text-center">
                  <?php 
                  $statusColors = [
                    'completed' => 'success',
                    'pending' => 'warning',
                    'processing' => 'info',
                    'shipped' => 'primary',
                    'cancelled' => 'danger'
                  ];
                  foreach ($revenueByStatus as $status): 
                    $color = $statusColors[strtolower($status['status'])] ?? 'secondary';
                  ?>
                  <div class="col-4 mb-2">
                    <div class="text-<?php echo $color; ?> fw-bold">
                      <?php echo $status['order_count']; ?>
                    </div>
                    <div class="text-muted small">
                      <?php echo ucfirst($status['status']); ?>
                    </div>
                  </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="card mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Recent Orders</h6>
            <a href="allOrder.php" class="btn btn-sm btn-outline-primary">View All</a>
          </div>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php while($order = mysqli_fetch_assoc($latestOrdersResult)): 
                  $statusClass = strtolower($order['status']) === 'completed' ? 'success' : 
                                (strtolower($order['status']) === 'pending' ? 'warning' : 
                                (strtolower($order['status']) === 'cancelled' ? 'danger' : 'info'));
                ?>
                <tr>
                  <td>#<?php echo $order['id']; ?></td>
                  <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                  <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                  <td>৳<?php echo number_format($order['total_amount'], 2); ?></td>
                  <td>
                    <span class="badge bg-<?php echo $statusClass; ?> status-badge">
                      <?php echo ucfirst($order['status']); ?>
                    </span>
                  </td>
                  <td>
                    <a href="order-details.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-eye"></i> View
                    </a>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- JavaScript Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Revenue Chart
      const revenueCtx = document.getElementById('revenueChart').getContext('2d');
      const revenueData = {
        labels: [<?php echo '"' . implode('","', array_column($monthlyRevenue, 'month')) . '"'; ?>],
        datasets: [{
          label: 'Revenue (৳)',
          data: [<?php echo implode(',', array_column($monthlyRevenue, 'revenue')); ?>],
          backgroundColor: 'rgba(108, 99, 255, 0.2)',
          borderColor: 'rgba(108, 99, 255, 1)',
          borderWidth: 2,
          tension: 0.3,
          fill: true
        }]
      };

      new Chart(revenueCtx, {
        type: 'line',
        data: revenueData,
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return '$' + context.raw.toLocaleString('en-US', {minimumFractionDigits: 2});
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return '$' + value.toLocaleString();
                }
              }
            }
          }
        }
      });

      // Order Status Chart
      const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
      const statusData = {
        labels: [<?php echo '"' . implode('","', array_column($revenueByStatus, 'status')) . '"'; ?>],
        datasets: [{
          data: [<?php echo implode(',', array_column($revenueByStatus, 'order_count')); ?>],
          backgroundColor: [
            'rgba(40, 167, 69, 0.7)',
            'rgba(255, 193, 7, 0.7)',
            'rgba(23, 162, 184, 0.7)',
            'rgba(108, 99, 255, 0.7)',
            'rgba(220, 53, 69, 0.7)'
          ],
          borderWidth: 1
        }]
      };

      new Chart(statusCtx, {
        type: 'doughnut',
        data: statusData,
        options: {
          responsive: true,
          cutout: '70%',
          plugins: {
            legend: {
              display: false
            }
          }
        }
      });

      // Initialize tooltips
      var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>
</body>
</html>