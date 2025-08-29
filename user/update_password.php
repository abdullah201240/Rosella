<?php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['password_message'] = 'All fields are required';
        $_SESSION['password_message_type'] = 'danger';
        header('Location: profile.php');
        exit();
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['password_message'] = 'New password and confirm password do not match';
        $_SESSION['password_message_type'] = 'danger';
        header('Location: profile.php');
        exit();
    }
    
    // Get current password hash from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password in database
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->bind_param('si', $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['password_message'] = 'Password updated successfully';
                $_SESSION['password_message_type'] = 'success';
            } else {
                $_SESSION['password_message'] = 'Failed to update password. Please try again.';
                $_SESSION['password_message_type'] = 'danger';
            }
            $update_stmt->close();
        } else {
            $_SESSION['password_message'] = 'Current password is incorrect';
            $_SESSION['password_message_type'] = 'danger';
        }
    } else {
        $_SESSION['password_message'] = 'User not found';
        $_SESSION['password_message_type'] = 'danger';
    }
    
    $stmt->close();
}

header('Location: profile.php');
exit();
?>
