<?php
session_start();
require_once '../includes/db.php';

// Check role authorization
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
    header('Location: login.php');
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $new_status = isset($_POST['new_status']) ? trim($_POST['new_status']) : '';

    // Define allowed statuses
    $allowed_statuses = ['pending', 'confirmed', 'canceled'];

    if ($order_id > 0 && in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param('si', $new_status, $order_id);

        if (!$stmt->execute()) {
            // Log the error or notify admin
            error_log("Failed to update order status for order ID: $order_id");
        }

        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid order ID or status.";
    }
}

// Redirect user back to admin dashboard
header('Location: admin_dashboard.php');
exit;
?>