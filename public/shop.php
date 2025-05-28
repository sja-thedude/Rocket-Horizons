<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';

$csrf_token = generateCSRFToken();

$products = [
    [
        'id' => 1,
        'name' => t('product_orbital_name'),
        'price' => 250000.00,
        'description' => t('product_orbital_description'),
    ],
    [
        'id' => 2,
        'name' => t('product_moon_name'),
        'price' => 1500000.00,
        'description' => t('product_moon_description'),
    ],
    [
        'id' => 3,
        'name' => t('product_mars_name'),
        'price' => 5000000.00,
        'description' => t('product_mars_description'),
    ],
];
?>
<div class="con">
<h2>Shop</h2>
</div>
<style>
    .product-list {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        padding: 20px;
    }
    .product-item {
        border: 1px solid #ccc;
        padding: 15px;
        width: 250px;
        border-radius: 8px;
    }
</style>

<div class="product-list">
    <?php foreach ($products as $product): ?>
        <div class="product-item">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p>Price: $<?php echo number_format($product['price'], 2); ?></p>
            <form method="post" action="cart.php">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <button class="cartbtn" type="submit">Add to Cart</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
<?php require_once '../includes/footer.php'; ?>