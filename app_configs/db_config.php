<?php
require __DIR__ . '/../vendor/autoload.php';

// Initialize Dotenv library for local development only
if (file_exists(__DIR__ . '/.env')) {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
  $dotenv->load();
}

// Database connection configuration
if (getenv("DATABASE_URL")) {
  $url = parse_url(getenv("DATABASE_URL"));

  $host = $url['host'];
  $dbname = substr($url['path'], 1); // Removes the leading slash
  $username = $url['user'];
  $password = $url['pass'];
} else {
  // Fall back to .env settings if not running on Heroku
  $host = $_ENV['DB_HOST'];
  $dbname = $_ENV['DB_NAME'];
  $username = $_ENV['DB_USER'];
  $password = $_ENV['DB_PASSWORD'];
}

// Establish database connection
try {
  $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
?>