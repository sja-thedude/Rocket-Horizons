<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/db.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<p>Your cart is empty. <a href='shop.php'>Go back to shop</a></p>";
    require_once '../includes/footer.php';
    exit;
}

$productsInCart = [];
$total = 0;

$ids = array_keys($cart);
if (empty($ids)) {
    echo "<p>Invalid cart data. <a href='shop.php'>Go back to shop</a></p>";
    require_once '../includes/footer.php';
    exit;
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $conn->prepare("SELECT id, name, price FROM products WHERE id IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $productId = $row['id'];
    $quantity = $cart[$productId];
    $subtotal = $row['price'] * $quantity;

    $productsInCart[] = [
        'id' => $productId,
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $quantity,
        'subtotal' => $subtotal
    ];

    $total += $subtotal;
}
?>

<h2>Checkout</h2>

<style>
table {
    width: 80%;
    margin: 20px auto;
    border-collapse: collapse;
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
}
th {
    background-color: #f2f2f2;
}
#paypal-button-container {
    text-align: center;
    margin-top: 30px;
}
</style>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productsInCart as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>$<?= number_format($item['price'], 2) ?></td>
                <td>$<?= number_format($item['subtotal'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
            <td><strong>$<?= number_format($total, 2) ?></strong></td>
        </tr>
    </tbody>
</table>

<div style="display: flex; justify-content: center; margin-top: 30px;">
    <div id="paypal-button-container"></div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=AaUQkwSGg7AYprW-3wXcdSY6_6b5RlbWuX5oGqJseb0GfiL0cV8s06-tNyRG6XNRcrG8fOQSKrdcAwCn&currency=USD"></script>
<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= number_format($total, 2, '.', '') ?>'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(details) {
            return fetch('save_order.php', {
                method: 'post',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    orderID: data.orderID,
                    payerName: details.payer.name.given_name,
                    payerEmail: details.payer.email_address,
                    amount: details.purchase_units[0].amount.value,
                    cart: <?= json_encode($cart) ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Transaction completed! Thank you for your order.');
                    window.location.href = 'thank_you.php';
                } else {
                    alert('There was an error processing your order.');
                }
            });
        });
    }
}).render('#paypal-button-container');
</script>

<?php require_once '../includes/footer.php'; ?>