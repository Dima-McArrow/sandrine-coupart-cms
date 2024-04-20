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
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['name']) && !empty($_POST['name'])) {
    // Adding a new diet type
    $name = $_POST['name'];

    try {
      $stmt = $pdo->prepare("INSERT INTO DietTypes (name) VALUES (:name)");
      $stmt->bindParam(':name', $name);
      $stmt->execute();

      $message = "Type de régime ajouté avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de l'ajout du type de régime : " . $e->getMessage();
    }
  } elseif (isset($_POST['delete_id'])) {
    // Deleting a diet type
    $id = $_POST['delete_id'];

    try {
      $stmt = $pdo->prepare("DELETE FROM DietTypes WHERE id = :id");
      $stmt->bindParam(':id', $id, PDO::PARAM_INT);
      $stmt->execute();

      $message = "Type de régime supprimé avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de la suppression du type de régime : " . $e->getMessage();
    }
  }
}

// Fetch all diet types from the database
$dietTypes = [];
try {
  $stmt = $pdo->query("SELECT id, name FROM DietTypes ORDER BY name");
  $dietTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Erreur lors de la récupération des types de régime : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des types de régime</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2>Gestion des types de régime</h2>
    <?php if ($message): ?>
      <div class="alert alert-info mt-2"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form to add new diet type -->
    <form action="" method="post" class="mb-3">
      <div class="mb-3">
        <label for="name" class="form-label">Nom du type de régime :</label>
        <input type="text" class="form-control" id="name" name="name" required>
      </div>
      <button type="submit" class="btn btn-primary">Ajouter le type de régime</button>
    </form>

    <!-- List of diet types -->
    <h3>Liste des types de régime</h3>
    <?php if ($dietTypes): ?>
      <ul class="list-group">
        <?php foreach ($dietTypes as $dietType): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($dietType['name']); ?>
            <form action="" method="post" style="display: inline;">
              <input type="hidden" name="delete_id" value="<?= $dietType['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aucun type de régime trouvé.</p>
    <?php endif; ?>
    <div class="row mt-5 mb-3">
      <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </div>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>