<?php
require_once '../includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo '<div class="container">';

echo "<h2>" . t('dashboard') . "</h2>";
echo "<p>" . t('welcome') . ", " . htmlspecialchars($_SESSION['username']) . "!</p>";

// Check role and show message accordingly
if ($_SESSION['role'] === 'admin') {
    echo "<p>You have admin access. <a href='admin_dashboard.php'>Go to Admin Dashboard</a></p>";
} elseif ($_SESSION['role'] === 'owner') {
    echo "<p>You have owner access. You can manage users and content.</p>";
} elseif ($_SESSION['role'] === 'employee') {
    echo "<p>You have employee access. You can manage tasks assigned to you.</p>";
} else {
    echo "<p>You have regular user access.</p>";
}

echo '<p><a href="my_orders.php">My Orders</a> | <a href="logout.php">Logout</a></p>';

echo '</div>'; 

require_once '../includes/footer.php';
?>