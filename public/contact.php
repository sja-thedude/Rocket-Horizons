<?php
require_once '../includes/header.php';
require_once '../includes/csrf.php';

$csrf_token = generateCSRFToken();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $submitted_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($submitted_token)) {
        $error = 'Invalid CSRF token.';
    } elseif (empty($name) || empty($email) || empty($message)) {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $success = 'Thank you for contacting us! We will get back to you shortly.';
    }
}
?>

<h2 class='title-contact'><?php echo t('title-contact'); ?></h2>

<?php if ($error): ?>
    <p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?=htmlspecialchars($success)?></p>
<?php endif; ?>

<form method="post" action="" class="contact-form">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <label for="name"><?php echo t('contact-name'); ?></label><br>
    <input type="text" id="name" name="name" required value="<?=htmlspecialchars($name ?? '')?>"><br><br>

    <label for="email"><?php echo t('contact-email'); ?></label><br>
    <input type="email" id="email" name="email" required value="<?=htmlspecialchars($email ?? '')?>"><br><br>

    <label for="message"><?php echo t('contact-message'); ?></label><br>
    <textarea id="message" name="message" rows="5" required><?=htmlspecialchars($message ?? '')?></textarea><br><br>

    <input class="submit-btn" type="submit" value="<?php echo t('send_message'); ?>">
</form>

<?php require_once '../includes/footer.php'; ?>