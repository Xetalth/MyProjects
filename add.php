<?php
session_start();

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

$category = ''; // add.php'de form yok artık, kategori durumu tutulmayacak
?>

<!DOCTYPE html>
<html>
<?php include('templates/header.php') ?>

<section class="container grey-text">
    <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Choose a Cozy Share</h4>
    <div class="container">
        <div class="center">
            <a href="add_food_recipe.php" class="btn hover-effect brand z-depth-1">
                <span>Food Recipe</span>
            </a>
            <a href="add_travel.php" class="btn brand hover-effect z-depth-1">
                <span>Vacotion/View</span>
            </a>
            <a href="add_book.php" class="btn brand hover-effect z-depth-1">
                <span>Book</span>
            </a>
            <a href="add_movie.php" class="btn brand hover-effect z-depth-1">
                <span> Movies & Series</span>
            </a>
            <a href="add_spot.php" class="btn brand hover-effect z-depth-1">
                <span> My Spot</span>
            </a>
        </div>
    </div>
</section>

<?php include('templates/footer.php') ?>
</html>
