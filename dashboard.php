<?php
session_start();

// Vérifiez si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit();
}

$user = $_SESSION['user']; // Obtenir les données de session utilisateur
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sandrine Coupart - Gestion</title>
  <link rel="icon" type="image/x-icon" href="./img/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <hr>
    <h1 class="mb-4">Bonjour <strong>
        <?php echo htmlspecialchars($user['name']); ?>
      </strong></h1>
    <hr>
    <div class="list-group">
      <?php if ($user): ?>
        <a href="manage_employees.php" class="list-group-item list-group-item-action">Gestion d'administrateur</a>
        <a href="manage_users.php" class="list-group-item list-group-item-action">Gestion d'utilisateurs</a>
        <a href="allergenes.php" class="list-group-item list-group-item-action">Gestion d'allergènes</a>
        <a href="manage_diet_types.php" class="list-group-item list-group-item-action">Gestion des types de régime</a>
        <a href="recipes.php" class="list-group-item list-group-item-action">Gestion des recettes</a>
        <a href="messages.php" class="list-group-item list-group-item-action">Gestion des messages</a>
        <a href="testim_manage.php" class="list-group-item list-group-item-action">Gestion des témoignages</a>
        <a href="reviews_manage.php" class="list-group-item list-group-item-action">Gestion des avis</a>
      <?php endif; ?>
    </div>
    <a href="logout.php" class="btn btn-danger mt-3">Logout</a>
  </div>
  <script src="./bootstrap/js/bootstrap.min.js"></script>
</body>
</html>