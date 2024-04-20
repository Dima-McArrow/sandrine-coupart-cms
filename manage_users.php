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
  if (!empty($_POST['first_name'])) {
    // Add user
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $birthDate = $_POST['birth_date'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt = $pdo->prepare("INSERT INTO Users (first_name, last_name, email, birth_date, password_hash) VALUES (?, ?, ?, ?, ?)");
      $stmt->execute([$firstName, $lastName, $email, $birthDate, $passwordHash]);
      $userId = $pdo->lastInsertId(); // Get the ID of the newly inserted user

      // Handle diet types
      if (!empty($_POST['diet_types'])) {
        $stmtDiet = $pdo->prepare("INSERT IGNORE INTO UserDietTypes (user_id, diet_type_id) VALUES (?, ?)");
        foreach ($_POST['diet_types'] as $dietTypeId) {
          $stmtDiet->execute([$userId, $dietTypeId]);
        }
      }

      // Handle allergens
      if (!empty($_POST['allergens'])) {
        $stmtAllergen = $pdo->prepare("INSERT IGNORE INTO UserAllergens (user_id, allergen_id) VALUES (?, ?)");
        foreach ($_POST['allergens'] as $allergenId) {
          $stmtAllergen->execute([$userId, $allergenId]);
        }
      }

      $message = "Utilisateur ajouté avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
    }
  } elseif (isset($_POST['delete_id'])) {
    // Delete user
    $deleteId = $_POST['delete_id'];

    try {
      $stmt = $pdo->prepare("DELETE FROM Users WHERE id = ?");
      $stmt->execute([$deleteId]);
      $message = "Utilisateur supprimé avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de la suppression de l'utilisateur : " . $e->getMessage();
    }
  }
}

// Fetch all users with their distinct diet types and allergens
try {
  $stmt = $pdo->query("
    SELECT 
      u.id, u.first_name, u.last_name, u.email, 
      GROUP_CONCAT(DISTINCT dt.name ORDER BY dt.name SEPARATOR ', ') AS diet_types, 
      GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ') AS allergens 
    FROM 
      Users u 
    LEFT JOIN UserDietTypes udt ON u.id = udt.user_id 
    LEFT JOIN DietTypes dt ON udt.diet_type_id = dt.id 
    LEFT JOIN UserAllergens ua ON u.id = ua.user_id 
    LEFT JOIN Allergens a ON ua.allergen_id = a.id 
    GROUP BY u.id
  ");
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Erreur lors de la récupération des utilisateurs : " . $e->getMessage();
}

// Fetch Diet Types and Allergens
try {
  $dietTypes = $pdo->query("SELECT id, name FROM DietTypes ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
  $allergens = $pdo->query("SELECT id, name FROM Allergens ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message .= " Erreur lors de la récupération des options : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des utilisateurs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2>Gestion des utilisateurs</h2>
    <?php if ($message): ?>
      <div class="alert alert-info mt-2"><?= $message ?></div>
    <?php endif; ?>
    <form action="" method="post" class="mb-3">
      <!-- Form fields for adding a new user -->
      <div class="mb-3">
        <label for="first_name" class="form-label">Prénom :</label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
      </div>
      <div class="mb-3">
        <label for="last_name" class="form-label">Nom de famille :</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email :</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="birth_date" class="form-label">Date de naissance :</label>
        <input type="date" class="form-control" id="birth_date" name="birth_date" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3">
        <label>Types de régime :</label><br>
        <?php foreach ($dietTypes as $type): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="diet_types[]" value="<?= $type['id'] ?>"
              id="diet_type<?= $type['id'] ?>">
            <label class="form-check-label"
              for="diet_type<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mb-3">
        <label>Allergènes :</label><br>
        <?php foreach ($allergens as $allergen): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="allergens[]" value="<?= $allergen['id'] ?>"
              id="allergen<?= $allergen['id'] ?>">
            <label class="form-check-label"
              for="allergen<?= $allergen['id'] ?>"><?= htmlspecialchars($allergen['name']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary">Ajouter l'utilisateur</button>
    </form>
    <div class="row mt-5 mb-3">
      <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </div>
    <!-- List of users -->
    <h3>Liste des utilisateurs</h3>
    <?php if ($users): ?>
      <ul class="list-group mb-5">
        <?php foreach ($users as $user): ?>
          <li class="list-group-item">
            <?= htmlspecialchars($user['first_name']) . " " . htmlspecialchars($user['last_name']) . " - " . htmlspecialchars($user['email']); ?>
            <br>
            <b>Types de régime :</b> <?= htmlspecialchars($user['diet_types']); ?>
            <br>
            <b>Allergènes :</b> <?= htmlspecialchars($user['allergens']); ?>
            <form action="" method="post" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?= $user['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aucun utilisateur trouvé.</p>
    <?php endif; ?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>