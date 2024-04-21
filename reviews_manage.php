<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require './app_configs/db_config.php';

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
  case 'approve':
    if (isset($_GET['id'])) {
      $reviewId = $_GET['id'];
      $sql = "UPDATE Reviews SET is_validated = 1 WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$reviewId]);
      header("Location: ?action=fetch");
      exit();
    }
    break;

  case 'disapprove':
    if (isset($_GET['id'])) {
      $reviewId = $_GET['id'];
      $sql = "UPDATE Reviews SET is_validated = 0 WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$reviewId]);
      header("Location: ?action=fetch");
      exit();
    }
    break;

  case 'delete':
    if (isset($_GET['id'])) {
      $reviewId = $_GET['id'];
      $sql = "DELETE FROM Reviews WHERE id = ?";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$reviewId]);
      header("Location: ?action=fetch");
      exit();
    }
    break;

  case 'fetch':
  default:
    // Default case to fetch all reviews
    break;
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sandrine Coupart - Gestion des avis</title>
  <link rel="icon" type="image/x-icon" href="./img/favicon.svg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h1 class="mb-4">Gestion des avis</h1>
    <hr>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nom</th>
            <th scope="col">Âge</th>
            <th scope="col">Recette</th>
            <th scope="col">Commentaire</th>
            <th scope="col">Évaluation</th>
            <th scope="col">Approuvé</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          // Extend the SQL query to include recipe title and calculate user's age
          $stmt = $pdo->query("SELECT Reviews.id, Users.first_name, Users.birth_date, Recipes.title as recipe_title, Reviews.comment, Reviews.rating, Reviews.is_validated FROM Reviews JOIN Users ON Reviews.user_id = Users.id JOIN Recipes ON Reviews.recipe_id = Recipes.id ORDER BY Reviews.created_at DESC");
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $age = date_diff(date_create($row['birth_date']), date_create('today'))->y;
            echo "<tr>";
            echo "<th scope='row'>{$row['id']}</th>";
            echo "<td>{$row['first_name']}</td>";
            echo "<td>{$age}</td>";
            echo "<td>{$row['recipe_title']}</td>";
            echo "<td>{$row['comment']}</td>";
            echo "<td>{$row['rating']}</td>";
            echo "<td>" . ($row['is_validated'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>";
            if (!$row['is_validated']) {
              echo "<a href='?action=approve&id={$row['id']}' class='btn btn-sm btn-success me-2'>Approuver</a>";
            } else {
              echo "<a href='?action=disapprove&id={$row['id']}' class='btn btn-sm btn-warning me-2'>Désapprouver</a>";
            }
            echo "<a href='?action=delete&id={$row['id']}' class='btn btn-sm btn-danger' onclick=\"return confirm('⚠️ Êtes-vous sûr?');\">Supprimer</a>";
            echo "</td>";
            echo "</tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
    <div class="row">
      <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </div>
  </div>
  <script src="./bootstrap/js/bootstrap.min.js"></script>
</body>

</html>