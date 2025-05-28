<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';

$csrf_token = generateCSRFToken();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = 'user'; // default role on register
    }
    
    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already taken.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $username, $email, $hashed, $role);
            if ($stmt->execute()) {
                $success = 'Registration successful! <a href="login.php">Login here</a>.';
            } else {
                $error = 'Database error: ' . $conn->error;
            }
        }
        $stmt->close();
    }
}
?>
<div class="form-center-wrapper">
    <div class="form-container">
        <h2><?php echo t('register'); ?></h2>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php else: ?>
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <label><?php echo t('username'); ?>:</label><br>
            <input type="text" name="username" required><br><br>

            <label><?php echo t('email'); ?>:</label><br>
            <input type="email" name="email" required><br><br>

            <label><?php echo t('password'); ?>:</label><br>
            <input type="password" name="password" required><br><br>

            <input type="submit" value="<?php echo t('submit'); ?>">
        </form>
<?php endif; ?>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>
