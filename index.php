<?php
session_start();
require_once './app_configs/db_config.php';

ini_set('display_errors', 0);
error_reporting(E_ALL);


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = $_POST['email'];
  $password = $_POST['password'];

  // Fetch user from the database by email
  $query = "SELECT * FROM Employees WHERE email = :email LIMIT 1";
  $stmt = $pdo->prepare($query);
  $stmt->execute(['email' => $email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Verify the password
  if ($user && password_verify($password, $user['password_hash'])) {
    // Set session variable to identify the user
    $_SESSION['user'] = ['name' => $user['first_name'] . ' ' . $user['last_name']];
    header('Location: dashboard.php');
    exit();
  } else {
    $error = "🚫 L'e-mail ou le mot de passe n'est pas valide"; // Display error message if authentication fails
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sandrine Coupart - Login</title>
  <link rel="icon" type="image/x-icon" href="./img/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <img class="main_logo mx-auto d-block mt-5" style="width: 300px;" src="https://" alt="logo CMS" />
  <div class="container text-center">
    <h1 class="mt-5 mb-5">Bienvenue sur le CMS du site web Sandrine Coupart</h1>
    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
      </div>
    <?php endif; ?>
    <form action="index.php" method="post">
      <div class="mb-3 m-auto col-8 col-sm-6">
        <label for="email" class="form-label">Email :</label>
        <input type="email" id="email" name="email" class="form-control" required>
      </div>
      <div class="mb-3 m-auto col-8 col-sm-6">
        <label for="password" class="form-label">Password :</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
  </div>
</body>

</html>