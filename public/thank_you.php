<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/header.php';

// Make sure you have the order data passed, e.g. via GET params or session
$orderID = $_GET['orderID'] ?? null;
$payerName = $_GET['payerName'] ?? null;
$payerEmail = $_GET['payerEmail'] ?? null;
$amount = $_GET['amount'] ?? null;

// The cart details, you might store in session or localStorage (if using JS)
$cart = $_SESSION['cart'] ?? [];

if ($orderID && $payerName && $payerEmail && $amount && !empty($cart)) {
    // Save order logic here, similar to save_order.php but inline

    $conn->begin_transaction();

    try {
        $query = "INSERT INTO orders (paypal_order_id, payer_name, payer_email, amount, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssd", $orderID, $payerName, $payerEmail, $amount);
        $stmt->execute();
        $orderId = $stmt->insert_id;
        $stmt->close();

        $stmtItems = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
        foreach ($cart as $productId => $quantity) {
            $productId = (int)$productId;
            $quantity = (int)$quantity;
            $stmtItems->bind_param("iii", $orderId, $productId, $quantity);
            $stmtItems->execute();
        }
        $stmtItems->close();

        $conn->commit();

        // Clear the cart from session after saving order
        unset($_SESSION['cart']);

        echo "<p>Order saved successfully!</p>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<p>Failed to save order: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>Missing order information or cart is empty.</p>";
}
?>
<div class="container">
<h2>Thank you for your order!</h2>
<p class="container">Your payment has been processed successfully.</p>
<a class="shopbtn" href="shop.php">Continue Shopping</a>
<a class="ordersbtn" href="my_orders.php">View Your Orders</a>
</div>
<?php require_once '../includes/footer.php'; ?>