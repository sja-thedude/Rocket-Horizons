<?php
// cookie_consent.php

require_once '../includes/header.php';

// Handle AJAX request to set cookie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_cookie_consent'])) {
    setcookie('cookie_consent', 'yes', [
        'expires' => time() + 60 * 60 * 24 * 365, // 1 year
        'path' => '/',
        'secure' => true,        // requires HTTPS, set false if testing locally without HTTPS
        'httponly' => false,     // JS must read/write cookie for consent
        'samesite' => 'Strict'   // Strict or Lax as per your preference
    ]);
    echo json_encode(['status' => 'set']);
    exit;
}

// Check if cookie consent already given
$cookieAccepted = isset($_COOKIE['cookie_consent']) && $_COOKIE['cookie_consent'] === 'yes';
?>

<?php if (!$cookieAccepted): ?>
<div id="cookieConsent" style="position: fixed; bottom: 0; background: #222; color: white; width: 100%; padding: 15px; text-align: center; font-size: 14px; z-index: 9999;">
  This website uses cookies to ensure you get the best experience.
  <button onclick="acceptCookies()" style="background: #f1d600; border: none; padding: 8px 15px; margin-left: 10px; cursor: pointer; font-weight: bold;">Accept</button>
  <a href="/cookies.php" style="color:#f1d600; text-decoration: underline; margin-left: 15px;">Learn more</a>
</div>

<script>
function acceptCookies() {
  fetch('cookie_consent.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'set_cookie_consent=yes'
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'set') {
      document.getElementById('cookieConsent').style.display = 'none';
      location.reload();  // Reload page so consent is registered for all scripts
    }
  })
  .catch(error => {
    console.error('Error setting cookie consent:', error);
  });
}
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>