<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require './vendor/autoload.php';
require './app_configs/db_config.php'; // Adjust the path as needed

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Only load Dotenv if running locally and .env file exists
if (file_exists(__DIR__ . '/.env')) {
  $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/app_configs');
  $dotenv->load();
}

if (!isset($_SESSION['user'])) {
  header('Location: index.php');
  exit();
}

$message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (isset($_POST['title'])) {
    // Add a new recipe
    try {
      $pdo->beginTransaction();

      // Upload images to S3
      $imageUrls = uploadImagesToS3($_FILES['images']);

      // Insert recipe into database
      $stmt = $pdo->prepare("INSERT INTO Recipes (title, description, prep_time, rest_time, cook_time, image_url) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$_POST['title'], $_POST['description'], $_POST['prep_time'], $_POST['rest_time'], $_POST['cook_time'], $imageUrls[0] ?? null]);
      $recipeId = $pdo->lastInsertId();

      // Handle Ingredients, Steps, Diet Types, and Allergens
      handleIngredients($pdo, $recipeId, $_POST['ingredients']);
      handleSteps($pdo, $recipeId, $_POST['steps']);
      handleDietTypes($pdo, $recipeId, $_POST['diet_types']);
      handleAllergens($pdo, $recipeId, $_POST['allergens']);

      $pdo->commit();
      $message = "Recette ajoutée avec succès !";
    } catch (PDOException $e) {
      $pdo->rollBack();
      $message = "Erreur lors de l'ajout de la recette : " . $e->getMessage();
    }
  } elseif (isset($_POST['delete_id'])) {
    // Delete a recipe
    try {
      $pdo->beginTransaction();
      deleteRecipe($pdo, $_POST['delete_id']);
      $pdo->commit();
      $message = "Recette supprimée avec succès !";
    } catch (PDOException $e) {
      $pdo->rollBack();
      $message = "Erreur lors de la suppression de la recette : " . $e->getMessage();
    }
  }
}

// Fetch all recipes for display
$recipes = [];
try {
  $stmt = $pdo->query("SELECT * FROM Recipes");
  $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message .= " Erreur lors de la récupération des recettes : " . $e->getMessage();
}

// Fetch Diet Types and Allergens for form selection
$dietTypes = [];
$allergens = [];
try {
  $dietTypesStmt = $pdo->query("SELECT id, name FROM DietTypes ORDER BY name");
  $allergensStmt = $pdo->query("SELECT id, name FROM Allergens ORDER BY name");
  $dietTypes = $dietTypesStmt->fetchAll(PDO::FETCH_ASSOC);
  $allergens = $allergensStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $message .= " Erreur lors de la récupération des types de régimes ou des allergènes : " . $e->getMessage();
}

// Function to upload multiple images to S3
function uploadImagesToS3($images)
{
  // Fetch S3 credentials from environment variables set in Heroku
  $s3Region = getenv('S3_REGION');
  $s3Key = getenv('S3_KEY');
  $s3Secret = getenv('S3_SECRET');
  $s3Bucket = getenv('S3_BUCKET');

  $s3 = new S3Client([
    'version' => 'latest',
    'region' => $s3Region,
    'credentials' => [
      'key' => $s3Key,
      'secret' => $s3Secret,
    ],
  ]);

  $urls = [];
  foreach ($images['name'] as $index => $name) {
    $key = 'recipes/' . $name;
    try {
      $result = $s3->putObject([
        'Bucket' => $s3Bucket,
        'Key' => $key,
        'SourceFile' => $images['tmp_name'][$index],
        'ACL' => 'public-read'
      ]);
      $urls[] = $result->get('ObjectURL');
    } catch (AwsException $e) {
      // Handle possible errors here
    }
  }
  return $urls;
}


// Function to handle insertion of ingredients, steps, diet types, allergens
function handleIngredients($pdo, $recipeId, $ingredients)
{
  foreach ($ingredients as $ingredient) {
    $stmt = $pdo->prepare("INSERT INTO Ingredients (recipe_id, name, quantity, measure_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([
      $recipeId,
      $ingredient['name'],
      $ingredient['quantity'],
      $ingredient['measure_type']
    ]);
  }
}

function handleSteps($pdo, $recipeId, $steps)
{
  foreach ($steps as $step) {
    $stmt = $pdo->prepare("INSERT INTO Steps (recipe_id, step_number, instruction) VALUES (?, ?, ?)");
    $stmt->execute([$recipeId, $step['step_number'], $step['instruction']]);
  }
}

function handleDietTypes($pdo, $recipeId, $dietTypes)
{
  if (isset($dietTypes) && is_array($dietTypes)) {
    foreach ($dietTypes as $dietType) {
      $stmt = $pdo->prepare("INSERT INTO RecipeDietTypes (recipe_id, diet_type_id) VALUES (?, ?)");
      $stmt->execute([$recipeId, $dietType]);
    }
  }
}

function handleAllergens($pdo, $recipeId, $allergens)
{
  if (isset($allergens) && is_array($allergens)) {
    foreach ($allergens as $allergen) {
      $stmt = $pdo->prepare("INSERT INTO RecipeAllergens (recipe_id, allergen_id) VALUES (?, ?)");
      $stmt->execute([$recipeId, $allergen]);
    }
  }
}

function deleteRecipe($pdo, $recipeId)
{
  $stmt1 = $pdo->prepare("DELETE FROM Ingredients WHERE recipe_id = ?");
  $stmt2 = $pdo->prepare("DELETE FROM Steps WHERE recipe_id = ?");
  $stmt3 = $pdo->prepare("DELETE FROM RecipeDietTypes WHERE recipe_id = ?");
  $stmt4 = $pdo->prepare("DELETE FROM RecipeAllergens WHERE recipe_id = ?");
  $stmt5 = $pdo->prepare("DELETE FROM Recipes WHERE id = ?");
  $stmt1->execute([$recipeId]);
  $stmt2->execute([$recipeId]);
  $stmt3->execute([$recipeId]);
  $stmt4->execute([$recipeId]);
  $stmt5->execute([$recipeId]);
}

// Define the function to retrieve ingredients by recipe ID
function getIngredientsByRecipe($pdo, $recipeId)
{
  $stmt = $pdo->prepare("SELECT * FROM Ingredients WHERE recipe_id = ?");
  $stmt->execute([$recipeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define the function to retrieve steps by recipe ID
function getStepsByRecipe($pdo, $recipeId)
{
  $stmt = $pdo->prepare("SELECT * FROM Steps WHERE recipe_id = ?");
  $stmt->execute([$recipeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define the function to retrieve diet types by recipe ID
function getDietTypesByRecipe($pdo, $recipeId)
{
  $stmt = $pdo->prepare("SELECT DietTypes.id, DietTypes.name FROM DietTypes INNER JOIN RecipeDietTypes ON DietTypes.id = RecipeDietTypes.diet_type_id WHERE RecipeDietTypes.recipe_id = ?");
  $stmt->execute([$recipeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Define the function to retrieve allergens by recipe ID
function getAllergensByRecipe($pdo, $recipeId)
{
  $stmt = $pdo->prepare("SELECT Allergens.id, Allergens.name FROM Allergens INNER JOIN RecipeAllergens ON Allergens.id = RecipeAllergens.allergen_id WHERE RecipeAllergens.recipe_id = ?");
  $stmt->execute([$recipeId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestionnaire de recettes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <div class="container">
    <h1 class="mt-5">Gestionnaire de recettes</h1>
    <?php if ($message): ?>
      <div class="alert alert-info mt-2"><?= $message ?></div>
    <?php endif; ?>
    <h2 class="mt-5">Ajouter une recette</h2>
    <form method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="title" class="form-label">Titre :</label>
        <input type="text" class="form-control" id="title" name="title" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description :</label>
        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
      </div>
      <div class="row mb-3">
        <div class="col">
          <label for="prep_time" class="form-label">Temps de préparation (mins) :</label>
          <input type="number" class="form-control" id="prep_time" name="prep_time" required>
        </div>
        <div class="col">
          <label for="rest_time" class="form-label">Temps de repos (mins) :</label>
          <input type="number" class="form-control" id="rest_time" name="rest_time" required>
        </div>
        <div class="col">
          <label for="cook_time" class="form-label">Temps de cuisson (mins) :</label>
          <input type="number" class="form-control" id="cook_time" name="cook_time" required>
        </div>
      </div>
      <div class="mb-3">
        <label for="images" class="form-label">Images :</label>
        <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
      </div>
      <div class="mb-3">
        <label for="ingredients" class="form-label">Ingrédients :</label>
        <button type="button" class="btn btn-primary" onclick="addIngredient()">Ajouter un ingrédient</button>
        <div id="ingredients-container"></div>
      </div>
      <div class="mb-3">
        <label for="steps" class="form-label">Étapes :</label>
        <button type="button" class="btn btn-primary" onclick="addStep()">Ajouter une étape</button>
        <div id="steps-container"></div>
      </div>
      <div class="mb-3">
        <label class="form-label">Types de régime :</label><br>
        <?php foreach ($dietTypes as $dietType): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="<?= $dietType['name'] ?>" name="diet_types[]"
              value="<?= $dietType['id'] ?>">
            <label class="form-check-label" for="<?= $dietType['name'] ?>"><?= $dietType['name'] ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="mb-3">
        <label class="form-label">Allergènes :</label><br>
        <?php foreach ($allergens as $allergen): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="<?= $allergen['name'] ?>" name="allergens[]"
              value="<?= $allergen['id'] ?>">
            <label class="form-check-label" for="<?= $allergen['name'] ?>"><?= $allergen['name'] ?></label>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary">Ajouter la recette</button>
      <div class="row mt-5 mb-3">
        <a href="dashboard.php" class="btn btn-secondary">Retour</a>
      </div>
    </form>
    <h2 class="mt-5">Recettes</h2>
    <div class="container d-flex gap-3">
      <?php foreach ($recipes as $recipe): ?>
        <div class="card mt-3 mb-5" style="width: 22rem;">
          <div class="card-body">
            <h3 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h3>
            <p class="card-text"><?= htmlspecialchars($recipe['description']) ?></p>
            <p class="card-text">Temps de préparation (mins) : <?= intval($recipe['prep_time']) ?> mins</p>
            <p class="card-text">Temps de repos (mins) : <?= intval($recipe['rest_time']) ?> mins</p>
            <p class="card-text">Temps de cuisson (mins) : <?= intval($recipe['cook_time']) ?> mins</p>
            <h5>Ingrédients</h5>
            <ul>
              <?php $ingredients = getIngredientsByRecipe($pdo, $recipe['id']); ?>
              <?php if (!empty($ingredients)): ?>
                <?php foreach ($ingredients as $ingredient): ?>
                  <li><?= htmlspecialchars($ingredient['name']) ?> - <?= htmlspecialchars($ingredient['quantity']) ?>
                    <?= htmlspecialchars($ingredient['measure_type']) ?>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
            <h5>Étapes</h5>
            <ol>
              <?php $steps = getStepsByRecipe($pdo, $recipe['id']); ?>
              <?php if (!empty($steps)): ?>
                <?php foreach ($steps as $step): ?>
                  <li><?= htmlspecialchars($step['instruction']) ?></li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ol>
            <?php if ($recipe['image_url']): ?>
              <img src="<?= htmlspecialchars($recipe['image_url']) ?>" class="card-img-top" style="width: 300px;"
                alt="Recipe Image">
            <?php endif; ?>
            <h5 class="mt-3">Diet Types</h5>
            <ul>
              <?php $dietTypes = getDietTypesByRecipe($pdo, $recipe['id']); ?>
              <?php if (!empty($dietTypes)): ?>
                <?php foreach ($dietTypes as $dietType): ?>
                  <li><?= htmlspecialchars($dietType['name']) ?></li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
            <h5>Allergènes</h5>
            <ul>
              <?php $allergens = getAllergensByRecipe($pdo, $recipe['id']); ?>
              <?php if (!empty($allergens)): ?>
                <?php foreach ($allergens as $allergen): ?>
                  <li><?= htmlspecialchars($allergen['name']) ?></li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
            <form method="post">
              <input type="hidden" name="delete_id" value="<?= $recipe['id'] ?>">
              <button type="submit" class="btn btn-danger">Supprimer la recette</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    let ingredientIndex = 0;
    function addIngredient() {
      const container = document.getElementById('ingredients-container');
      const inputGroup = document.createElement('div');
      inputGroup.className = 'input-group mb-3';
      inputGroup.innerHTML = `
                <input type="text" class="form-control" name="ingredients[${ingredientIndex}][name]" placeholder="Ingredient Name" required>
                <input type="number" class="form-control" name="ingredients[${ingredientIndex}][quantity]" placeholder="Quantity" required>
                <select class="form-select" name="ingredients[${ingredientIndex}][measure_type]" required>
                    <option value="unit">Unit</option>
                    <option value="g">Grams</option>
                    <option value="kg">Kilograms</option>
                    <option value="ml">Milliliters</option>
                    <option value="l">Liters</option>
                </select>
                <button type="button" class="btn btn-danger" onclick="removeElement(this)">Remove</button>
            `;
      container.appendChild(inputGroup);
      ingredientIndex++;
    }

    let stepIndex = 0; // Added for step management
    function addStep() {
      const container = document.getElementById('steps-container');
      const inputGroup = document.createElement('div');
      inputGroup.className = 'input-group mb-3';
      inputGroup.innerHTML = `
                <input type="number" class="form-control mb-2" name="steps[${stepIndex}][step_number]" placeholder="Step Number" required>
                <input type="text" class="form-control mb-2" name="steps[${stepIndex}][instruction]" placeholder="Step Description" required>
                <button type="button" class="btn btn-danger" onclick="removeElement(this)">Remove</button>
            `;
      container.appendChild(inputGroup);
      stepIndex++; // Increment index for each new step
    }

    function removeElement(element) {
      element.parentNode.remove();
    }
  </script>
</body>

</html>