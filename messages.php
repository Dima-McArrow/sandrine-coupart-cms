<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require './vendor/autoload.php';
require './app_configs/db_config.php'; // Adjust the path as needed

$pdo; // Ensure that $pdo is defined and connected properly to your database

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit();
}

$message = '';

// Handle Delete Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
  $deleteId = $_POST['delete_id'];
  try {
    $stmt = $pdo->prepare("DELETE FROM Messages WHERE id = ?");
    $stmt->execute([$deleteId]);
    $message = "Message deleted successfully.";
  } catch (PDOException $e) {
    $message = "Error deleting message: " . $e->getMessage();
  }

  // Optional: Redirect to avoid resubmission on page refresh
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
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
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Message Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <h1 class="mt-5">Message Manager</h1>
    <?php if ($message): ?>
      <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>
    <h2 class="mt-5">Messages</h2>
    <?php foreach ($messages as $message): ?>
      <div class="card mt-3 mb-5">
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($message['first_name']) ?>
            <?= htmlspecialchars($message['last_name']) ?></h5>
          <h6 class="card-subtitle mb-2 text-muted">
            <a href="mailto:<?= htmlspecialchars($message['email']) ?>"><?= htmlspecialchars($message['email']) ?></a>
          </h6>
          <p class="card-text"><?= htmlspecialchars($message['message']) ?></p>
          <p class="card-text"><small class="text-muted"><?= $message['created_at'] ?></small></p>
          <!-- Delete form -->
          <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this message?');">
            <input type="hidden" name="delete_id" value="<?= $message['id'] ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="row mb-5">
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
  </div>
</body>

</html>