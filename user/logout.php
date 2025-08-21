<?php
session_start();

// Unset user session variables
unset($_SESSION['user_id']);
unset($_SESSION['user_name']);

// Optionally destroy session but keep cart session if needed
// session_destroy();

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
header('Location: ' . $redirect);
exit();
?>