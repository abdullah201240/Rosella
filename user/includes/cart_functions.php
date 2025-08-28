<?php
// Cart utility functions

function getCartCount($conn, $session_id) {
    if (!$conn || $conn->connect_error) {
        error_log("Invalid database connection in getCartCount");
        return 0;
    }
    $sql = "SELECT SUM(quantity) as total FROM carts WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['total'] ? (int)$row['total'] : 0;
    }
    return 0;
}

function addToCart($conn, $session_id, $product_id, $product_name, $product_price, $product_image, $quantity = 1) {
    if (!$conn || $conn->connect_error) {
        error_log("Invalid database connection in addToCart");
        return false;
    }
    // Check if the product is already in the cart for this session
    $sql_check = "SELECT * FROM carts WHERE session_id = ? AND product_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $session_id, $product_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Update the quantity if the product is already in the cart
        $sql_update = "UPDATE carts SET quantity = quantity + ? WHERE session_id = ? AND product_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("isi", $quantity, $session_id, $product_id);
        return $stmt_update->execute();
    } else {
        // Insert the product into the cart
        $sql_insert = "INSERT INTO carts (session_id, product_id, product_name, product_price, product_image, quantity) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sisdsi", $session_id, $product_id, $product_name, $product_price, $product_image, $quantity);
        return $stmt_insert->execute();
    }
}
?>
