<?php
require __DIR__ . '/../vendor/autoload.php';

// Initialize Dotenv library and load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection configuration
$host = $_ENV['DB_HOST']; // Database host from .env
$dbname = $_ENV['DB_NAME']; // Database name from .env
$username = $_ENV['DB_USER']; // Database username from .env
$password = $_ENV['DB_PASSWORD']; // Database password from .env

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>