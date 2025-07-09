<?php
session_start();
include('config/db_connect.php');

if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

$userid = $_SESSION['u_id'];
$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $ingredients = trim($_POST['ingredients'] ?? '');
    $recipe = trim($_POST['recipe'] ?? '');

    $form_data = ['title' => $title, 'ingredients' => $ingredients, 'recipe' => $recipe];

    // Basit doğrulamalar
    if ($title === '') {
        $errors['title'] = 'Title required.';
    } elseif (!preg_match('/^[\p{L}\s]+$/u', $title)) {
        $errors['title'] = 'The title can only contain letters and spaces.';
    }

    if ($ingredients === '') {
        $errors['ingredients'] = 'Ingredents required.';
    } elseif (!preg_match('/^([a-zA-Z\s]+)(,\s*[a-zA-Z\s]+)*$/', $ingredients)) {
        $errors['ingredients'] = 'Contents must be separated by commas.';
    }

    if ($recipe === '') {
        $errors['recipe'] = 'Recipe required';
    } elseif (!preg_match('/^[\p{L}\s\d.,;:!?()\'"\-\n\r]+$/u', $recipe)) {
        $errors['recipe'] = 'The recipe description contains invalid characters.';
    }

    if (empty($errors)) {
    // category tablosundan c_id al
    $category_name = 'Food Recipes';
    $category_query = $conn->prepare("SELECT c_id FROM category WHERE c_name = ?");
    $category_query->bind_param("s", $category_name);
    $category_query->execute();
    $result = $category_query->get_result();

    if ($row = $result->fetch_assoc()) {
        $c_id = $row['c_id'];

        // Güvenli veri
        $title_safe = $conn->real_escape_string($title);
        $ingredients_safe = $conn->real_escape_string($ingredients);
        $recipe_safe = $conn->real_escape_string($recipe);

        // Veritabanına ekle
        $stmt = $conn->prepare("INSERT INTO foods (c_id, title, ingredients, recipe, u_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("isssi", $c_id, $title_safe, $ingredients_safe, $recipe_safe, $userid);

        if ($stmt->execute()) {
            $new_food_id = $conn->insert_id;

            header('Location: index.php');
            exit;
        } else {
            $errors['db'] = "Veritabanı hatası: " . $stmt->error;
        }
    } else {
        $errors['category'] = 'Kategori bulunamadı.';
    }
}
}
?>

<!DOCTYPE html>
<html>
<?php include('templates/header.php') ?>

<section class="container">
    <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Share a Food Recipe</h4>
    <form action="add_food_recipe.php" method="POST" class="z-depth-1 form-card">
        <div class="form-card-content">
            <label class="text">Title:</label>
            <input class="text" type="text" name="title" value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>">
            <div class="red-text"><?php echo $errors['title'] ?? ''; ?></div>
            <label class="text">Ingredients (with comma separated):</label>
            <input class="text" type="text" name="ingredients" value="<?php echo htmlspecialchars($form_data['ingredients'] ?? ''); ?>">
            <div class="red-text"><?php echo $errors['ingredients'] ?? ''; ?></div>
            <label class="text">Recipe description:</label>
            <textarea name="recipe" class="text materialize-textarea"><?php echo htmlspecialchars($form_data['recipe'] ?? ''); ?></textarea>
            <div class="red-text"><?php echo $errors['recipe'] ?? ''; ?></div>

        <?php if (!empty($errors['db'])): ?>
            <div class="red-text"><?php echo $errors['db']; ?></div>
        <?php endif; ?>

        <div class="center">
            <input type="submit" value="Submit" class="btn hover-effect brand z-depth-1">
        </div>
        </div>
            
    </form>
</section>

<?php include('templates/footer.php') ?>
</html>
