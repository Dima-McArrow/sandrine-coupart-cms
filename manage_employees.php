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
  if (!empty($_POST['add_employee'])) {
    // Adding a new employee
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
      $stmt = $pdo->prepare("INSERT INTO Employees (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)");
      $stmt->execute([$firstName, $lastName, $email, $passwordHash]);
      $message = "Employé ajouté avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de l'ajout d'un employé : " . $e->getMessage();
    }
  } elseif (!empty($_POST['delete_id'])) {
    // Deleting an employee
    $id = $_POST['delete_id'];
    try {
      $stmt = $pdo->prepare("DELETE FROM Employees WHERE id = ?");
      $stmt->execute([$id]);
      $message = "Employé supprimé avec succès !";
    } catch (PDOException $e) {
      $message = "Erreur lors de la suppression de l'employé : " . $e->getMessage();
    }
  }
}

// Fetch all employees
$employees = [];
try {
  $stmt = $pdo->query("SELECT id, first_name, last_name, email FROM Employees");
  $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message = "Erreur lors de la récupération des employés : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des employés</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container mt-5">
    <h2>Gestion des employés</h2>
    <?php if ($message): ?>
      <div class="alert alert-info mt-2"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form to add new employee -->
    <form action="" method="post" class="mb-3">
      <input type="hidden" name="add_employee" value="1">
      <div class="mb-3">
        <label for="first_name" class="form-label">Prénom :</label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
      </div>
      <div class="mb-3">
        <label for="last_name" class="form-label">Nom de famille :</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email:</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password:</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Ajouter l'employé</button>
    </form>
    <div class="row mt-5 mb-3">
      <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </div>
    <!-- List of employees -->
    <h3>Liste des employés</h3>
    <?php if ($employees): ?>
      <ul class="list-group">
        <?php foreach ($employees as $employee): ?>
          <li class="list-group-item">
            <?= htmlspecialchars($employee['first_name']) . " " . htmlspecialchars($employee['last_name']) . " - " . htmlspecialchars($employee['email']); ?>
            <form action="" method="post" style="display: inline;">
              <input type="hidden" name="delete_id" value="<?= $employee['id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>Aucun employé trouvé.</p>
    <?php endif; ?>
  </div>

  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>