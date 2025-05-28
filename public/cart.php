<?php
session_start();

require_once '../includes/csrf.php';

$csrf_token = generateCSRFToken();


require_once '../includes/header.php';
require_once '../includes/db.php';

// Initialize cart if not already set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ✅ Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Check
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    die("Invalid CSRF token");
}

    // ✅ Remove item
    if (isset($_POST['action']) && $_POST['action'] === 'remove' && isset($_POST['product_id'])) {
        $removeId = (int)$_POST['product_id'];
        unset($_SESSION['cart'][$removeId]);
        header('Location: cart.php');
        exit();
    }

    // ✅ Add item
    if (isset($_POST['action']) && $_POST['action'] === 'add' && isset($_POST['product_id'])) {
        $productId = (int)$_POST['product_id'];
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
        header('Location: cart.php');
        exit();
    }
}

// Cart processing
$cart = $_SESSION['cart'];
$productsInCart = [];
$total = 0;

if (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $query = "SELECT id, name, price FROM products WHERE id IN ($ids)";
    $result = $conn->query($query);

    if (!$result) {
        die("Database Error: " . $conn->error);
    }

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
}
?>

<!-- Basic Styles for Readability -->
<style>
    table { border-collapse: collapse; width: 80%; margin: 20px auto; }
    th, td { padding: 8px 12px; border: 1px solid #ccc; text-align: center; }
    form { display: inline; }
</style>

<div class="container">
<h2>Your Cart</h2>

<?php if (empty($productsInCart)): ?>
    <p>Your cart is empty. <a href="shop.php">Go to shop</a></p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productsInCart as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>$<?= number_format($item['price'], 2) ?></td>
                    <td>$<?= number_format($item['subtotal'], 2) ?></td>
                    <td>
                        <form method="post" action="cart.php">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button class="removebtn" type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>

    <a class="checkoutbtn" href="checkout.php">Proceed to Checkout</a>
<?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>