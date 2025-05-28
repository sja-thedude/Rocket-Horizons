<?php
require_once __DIR__ . '/../vendor/autoload.php';  // go one level up to root/vendor/autoload.php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../'); // one level up from /includes
$dotenv->load();

$servername = $_ENV['DB_HOST'] ?? '';
$username = $_ENV['DB_USER'] ?? '';
$password = $_ENV['DB_PASS'] ?? '';
$dbname   = $_ENV['DB_NAME'] ?? '';

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Internal server error. Please try again later.");
}