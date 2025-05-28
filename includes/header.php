<?php
require_once __DIR__ . '/functions.php';  // adjust path as needed
secureSessionStart();

$cookieAccepted = isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'yes';

// Language selection
$lang = 'en'; // default
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'es', 'ar'])) {
    $lang = $_GET['lang'];
    $_SESSION['lang'] = $lang;
} elseif (isset($_SESSION['lang'])) {
    $lang = $_SESSION['lang'];
}

$translations = include __DIR__ . "/../languages/$lang.php";

function t($key) {
    global $translations;
    return $translations[$key] ?? $key;
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?php echo htmlspecialchars(t('home'), ENT_QUOTES, 'UTF-8'); ?> - Rocket Site</title>
    <link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php if (!$cookieAccepted): ?>
<div id="cookieConsent" style="position: fixed; bottom: 20px; width: 100%; background: #333; color: white; text-align: center; padding: 10px; z-index: 1000;">
  This website uses cookies to ensure you get the best experience. 
  <button onclick="acceptCookies()" style="margin-left: 10px;">Accept</button>
  <a href="/cookies.php" style="color:#f1d600; text-decoration:underline; margin-left: 15px;">Learn more</a>
</div>
<script>
function acceptCookies() {
  document.cookie = "cookie_consent=yes; path=/; max-age=" + (60*60*24*365) + "; Secure; SameSite=Strict";
  document.getElementById('cookieConsent').style.display = 'none';
}
</script>
<?php endif; ?>

<header>
    <nav>
        <a href="index.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('home'), ENT_QUOTES, 'UTF-8'); ?></a> |
        <a href="about.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('about_us'), ENT_QUOTES, 'UTF-8'); ?></a> |
        <a href="contact.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('contact_us'), ENT_QUOTES, 'UTF-8'); ?></a> |
        <a href="shop.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('shop'), ENT_QUOTES, 'UTF-8'); ?></a> |
        <a href="return_policy.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('return_policy'), ENT_QUOTES, 'UTF-8'); ?></a> |
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('dashboard'), ENT_QUOTES, 'UTF-8'); ?></a> |
            <a href="logout.php"><?php echo htmlspecialchars(t('logout'), ENT_QUOTES, 'UTF-8'); ?></a>
        <?php else: ?>
            <a href="login.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('login'), ENT_QUOTES, 'UTF-8'); ?></a> |
            <a href="register.php?lang=<?php echo urlencode($lang); ?>"><?php echo htmlspecialchars(t('register'), ENT_QUOTES, 'UTF-8'); ?></a>
        <?php endif; ?>
        <span style="float:right;">
            <a href="?lang=en">EN</a> | <a href="?lang=es">ES</a> | <a href="?lang=ar">AR</a>
        </span>
    </nav>
</header>
<main>