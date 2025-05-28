<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

// Read JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orderID'], $data['payerName'], $data['payerEmail'], $data['amount'], $data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

// Sanitize & validate input
$orderID = trim($data['orderID']);
$payerName = trim($data['payerName']);
$payerEmail = filter_var($data['payerEmail'], FILTER_VALIDATE_EMAIL);
$amount = floatval($data['amount']);
$cart = $data['cart'];

// Basic validations
if (!$orderID || !$payerName || !$payerEmail || $amount <= 0 || !is_array($cart) || empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid order data']);
    exit;
}

// Optional: Validate that cart product IDs and quantities are correct
$validCart = [];
$totalFromDB = 0.0;

foreach ($cart as $productId => $quantity) {
    $productId = (int)$productId;
    $quantity = (int)$quantity;

    if ($productId <= 0 || $quantity <= 0) {
        continue;
    }

    $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($price);

    if ($stmt->fetch()) {
        $totalFromDB += $price * $quantity;
        $validCart[$productId] = $quantity;
    }

    $stmt->close();
}

if (abs($totalFromDB - $amount) > 0.01) {
    echo json_encode(['success' => false, 'message' => 'Amount mismatch']);
    exit;
}

// Save order in DB
$conn->begin_transaction();

try {
    $query = "INSERT INTO orders (paypal_order_id, payer_name, payer_email, amount, created_at) VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssd", $orderID, $payerName, $payerEmail, $amount);
    $stmt->execute();
    $orderId = $stmt->insert_id;
    $stmt->close();

    $stmtItems = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    foreach ($validCart as $productId => $quantity) {
        $stmtItems->bind_param("iii", $orderId, $productId, $quantity);
        $stmtItems->execute();
    }
    $stmtItems->close();

    $conn->commit();

    // Clear cart session if needed
    unset($_SESSION['cart']);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save order: ' . htmlspecialchars($e->getMessage())
    ]);
}
?>