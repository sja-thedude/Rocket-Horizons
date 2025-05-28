<?php

function secureSessionStart() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_samesite' => 'Strict',
        ]);
    }
}

secureSessionStart(); // Automatically start a secure session when included

define('ROLE_ADMIN', 'admin');
define('ROLE_EMPLOYEE', 'employee');
define('ROLE_USER', 'user');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ADMIN;
}

function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_EMPLOYEE;
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_USER;
}
?>