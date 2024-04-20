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
// Handle allergen addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['allergenName'])) {
  $allergenName = $_POST['allergenName'];
  try {
    $stmt = $pdo->prepare("INSERT INTO Allergens (name) VALUES (:name)");
    $stmt->bindParam(':name', $allergenName);
    $stmt->execute();
    $message = "Allergène ajouté avec succès !";
  } catch (PDOException $e) {
    if ($e->getCode() == 23000) {
      $message = "Cet allergène existe déjà !";
    } else {
      $message = "Échec de l'ajout de l'allergène :" . $e->getMessage();
    }
  }
}

// Handle allergen deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
  $deleteId = $_POST['delete_id'];
  try {
    $stmt = $pdo->prepare("DELETE FROM Allergens WHERE id = :id");
    $stmt->bindParam(':id', $deleteId, PDO::PARAM_INT);
    $stmt->execute();
    $message = "Allergène supprimé avec succès !";
  } catch (PDOException $e) {
    $message = "Échec de la suppression de l'allergène :" . $e->getMessage();
  }
}

// Fetch all allergens from the database
$allergens = [];
try {
  $stmt = $pdo->query("SELECT id, name FROM Allergens ORDER BY name");
  $allergens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Échec de la récupération des allergènes :" . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion d'allergènes</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2>Ajouter un nouvel allergène</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="mb-3">
        <label for="allergenName" class="form-label">Nom de l'allergène :</label>
        <input type="text" class="form-control" id="allergenName" name="allergenName" required>
      </div>
      <button type="submit" class="btn btn-primary">Soumettre</button>
    </form>
    <?php if ($message): ?>
      <div class="alert alert-info mt-2"><?= $message ?></div>
    <?php endif; ?>
  </div>
  <div class="container mt-5">
    <h3>Tous les allergènes</h3>
    <?php if ($allergens): ?>
      <ul class="list-group">
        <?php foreach ($allergens as $allergen): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($allergen['name']); ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <input type="hidden" name="delete_id" value="<?= $allergen['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aucun allergène trouvé.</p>
    <?php endif; ?>
    <div class="row mt-5 mb-3">
      <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </div>
  </div>
  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>