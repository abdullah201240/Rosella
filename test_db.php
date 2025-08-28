<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include 'db.php';

// Test database connection
if ($conn) {
    echo "Database connection successful!<br>";
    
    // Test query
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "Test query executed successfully!";
    } else {
        echo "Test query failed: " . $conn->error;
    }
} else {
    echo "Database connection failed!";
}
?>
