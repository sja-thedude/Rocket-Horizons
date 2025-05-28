<?php
require_once '../includes/db.php';
require_once '../includes/header.php';
require_once '../includes/csrf.php';

$csrf_token = generateCSRFToken();

// Role check - allow only admin or employee
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
    header('Location: login.php');
    exit;
}

// Fetch all orders with user info and order items info (products/flights)
$query = "
    SELECT 
        o.id AS order_id,
        o.paypal_order_id,
        o.payer_name,
        o.payer_email,
        o.amount,
        o.order_status,
        o.created_at,
        GROUP_CONCAT(CONCAT(p.name, ' (Qty: ', oi.quantity, ')') SEPARATOR ',') AS items_ordered
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    GROUP BY o.id
    ORDER BY o.created_at DESC
";

$result = $conn->query($query);

?>

<h2>Admin Dashboard</h2>

<h3>Orders</h3>

<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">
    <thead>
    <tr>
        <th>Order ID</th>
        <th>PayPal Order ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Amount</th>
        <th>Items Ordered</th>
        <th>Status</th>
        <th>Ordered On</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['order_id']) ?></td>
        <td><?= htmlspecialchars($row['paypal_order_id']) ?></td>
        <td><?= htmlspecialchars($row['payer_name']) ?></td>
        <td><?= htmlspecialchars($row['payer_email']) ?></td>
        <td>$<?= htmlspecialchars(number_format($row['amount'], 2)) ?></td>
        <td>
            <?php
            // Explode by comma (no HTML here)
            $items = explode(',', $row['items_ordered'] ?? '');
            $safeItems = [];
            foreach ($items as $item) {
                $item = trim($item);
                if ($item !== '') {
                    $safeItems[] = htmlspecialchars($item);
                }
            }
            echo !empty($safeItems) ? implode('<br>', $safeItems) : '—';
            ?>
        </td>
        <td><?= htmlspecialchars(ucfirst($row['order_status'] ?? 'pending')) ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td>
            <form method="post" action="update_order.php" style="display:inline;">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <select name="new_status">
                    <option value="pending" <?= ($row['order_status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($row['order_status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="canceled" <?= ($row['order_status'] ?? '') === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                </select>
                <input type="submit" value="Update">
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>