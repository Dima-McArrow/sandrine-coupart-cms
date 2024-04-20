<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require './vendor/autoload.php';
require './app_configs/db_config.php'; // Adjust the path as needed

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit();
}

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['message'])) {
    // Add a new message
    try {
      // Insert message into database
      $stmt = $pdo->prepare("INSERT INTO Messages (first_name, last_name, email, message) VALUES (?, ?, ?, ?)");
      $stmt->execute([$_POST['first_name'], $_POST['last_name'], $_POST['email'], $_POST['message']]);
      $message = "Message sent successfully!";
    } catch (PDOException $e) {
      $message = "Error sending message: " . $e->getMessage();
    }
  }
}

// Fetch all messages for display
$messages = [];
try {
  $stmt = $pdo->query("SELECT * FROM Messages ORDER BY created_at DESC");
  $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message .= " Error retrieving messages: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Message Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <h1 class="mt-5">Message Manager</h1>

    <h2 class="mt-5">Send Message</h2>
    <form method="post">
      <div class="mb-3">
        <label for="first_name" class="form-label">First Name:</label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
      </div>
      <div class="mb-3">
        <label for="last_name" class="form-label">Last Name:</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Message:</label>
        <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
      </div>
      <button type="submit" class="btn btn-primary">Send Message</button>
    </form>

    <?php if ($message): ?>
      <p><?= $message ?></p>
    <?php endif; ?>

    <h2 class="mt-5">Messages</h2>
    <?php foreach ($messages as $message): ?>
      <div class="card mt-3">
        <div class="card-body">
          <h5 class="card-title"><?= $message['first_name'] ?>   <?= $message['last_name'] ?></h5>
          <h6 class="card-subtitle mb-2 text-muted"><?= $message['email'] ?></h6>
          <p class="card-text"><?= $message['message'] ?></p>
          <p class="card-text"><small class="text-muted"><?= $message['created_at'] ?></small></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</body>

</html>