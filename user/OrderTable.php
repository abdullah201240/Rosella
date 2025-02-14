<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database connection
include '../db.php';

// Fetch all orders from the database
$sql = "SELECT * FROM orders";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>
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
            </tr>";

    // Output data of each row
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

        echo "</td></tr>";
    }
    echo "</table>";
} else {
    echo "No orders found.";
}

// Close the database connection
$conn->close();
?>