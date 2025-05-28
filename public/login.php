<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';

$error = '';
$csrf_token = generateCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        die('Invalid CSRF token');
    }

    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password) || hash('sha256', $password) === $hashed_password) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = 'Incorrect password.';
        }
    } else {
        $error = 'User not found.';
    }
    $stmt->close();
}
?>
<div class="form-center-wrapper">
    <div class="form-container">
        <h2><?php echo t('login'); ?></h2>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            
            <label><?php echo t('username'); ?>:</label><br>
            <input type="text" name="username" required><br><br>

            <label><?php echo t('password'); ?>:</label><br>
            <input type="password" name="password" required><br><br>

            <input type="submit" value="<?php echo t('submit'); ?>">
        </form>
    </div>
</div>
<?php require_once '../includes/footer.php'; ?>