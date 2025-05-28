<?php
require_once '../includes/db.php';
require_once '../includes/header.php';

//session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user’s email to match orders
$stmtUser = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmtUser->bind_param('i', $user_id);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$userEmail = $userData['email'];
$stmtUser->close();

// Get orders with their items and product names
$query = "
    SELECT 
        o.id AS order_id, o.paypal_order_id, o.payer_name, o.amount, o.created_at,
        GROUP_CONCAT(CONCAT(p.name, ' (Qty: ', oi.quantity, ')') SEPARATOR ', ') AS products
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.payer_email = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $userEmail);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>My Space Flight Orders</h2>

<table border="1" cellpadding="5">
    <tr>
        <th>Order ID</th>
        <th>PayPal Order ID</th>
        <th>Name</th>
        <th>Amount</th>
        <th>Ordered On</th>
        <th>Products</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['order_id']) ?></td>
        <td><?= htmlspecialchars($row['paypal_order_id']) ?></td>
        <td><?= htmlspecialchars($row['payer_name']) ?></td>
        <td>$<?= htmlspecialchars(number_format($row['amount'], 2)) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td><?= htmlspecialchars($row['products']) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<?php
$stmt->close();
require_once '../includes/footer.php';
?>