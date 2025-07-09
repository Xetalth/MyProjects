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
    $description = trim($_POST['description'] ?? '');

    $form_data = ['title' => $title, 'description' => $description];

    // Doğrulamalar
    if ($title === '') {
        $errors['title'] = 'Title required';
    } elseif (!preg_match('/^[a-zA-Z0-9\s\p{L}]+$/u', $title)) {
        $errors['title'] = 'The title contains invalid characters.';
    }

    if ($description === '') {
        $errors['description'] = 'The comment section cannot be empty.';
    }

    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = 'The image could not be loaded.';
    } else {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $image_name = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_exts)) {
            $errors['image'] = 'Only JPG, PNG and GIF files are allowed.';
        }
    }

    if (empty($errors)) {
    $category_name = 'Movie';
    $category_query = $conn->prepare("SELECT c_id FROM category WHERE c_name = ?");
    $category_query->bind_param("s", $category_name);
    $category_query->execute();
    $result = $category_query->get_result();

    if ($row = $result->fetch_assoc()) {
        $c_id = $row['c_id'];

        $title_safe = $conn->real_escape_string($title);
        $description_safe = $conn->real_escape_string($description);
        $new_image_name = uniqid('movie_', true) . '.' . $ext;
        $upload_path = 'uploads/' . $new_image_name;
        move_uploaded_file($image_tmp, $upload_path);

        $stmt = $conn->prepare("INSERT INTO movie (c_id, title, m_description, m_image, u_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            $errors['db'] = "Prepare hatası: " . $conn->error;
        } else {
            $stmt->bind_param("isssi", $c_id, $title_safe, $description_safe, $new_image_name, $userid);

            if ($stmt->execute()) {
                // Son eklenen movie ID'sini al
                $new_id = $conn->insert_id;

                header('Location: index.php');
                exit;
            } else {
                $errors['db'] = "Veritabanı hatası: " . $stmt->error;
            }
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

<section class="container grey-text">
    <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Share a Movie / Series </h4>
    <form action="add_movie.php" method="POST" enctype="multipart/form-data" class="z-depth-1 form-card">
        <div class="form-card-content">
            <label class="text">Title:</label>
        <input class="text" type="text" name="title" value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>">
        <div class="red-text"><?php echo $errors['title'] ?? ''; ?></div>

        <label class="text">Comment for the content:</label>
        <textarea  name="description" class="text materialize-textarea"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
        <div class="red-text"><?php echo $errors['description'] ?? ''; ?></div>

        <label class="text">An image about Movie / Series:</label>
        
        <div class="file-field input-field">
                    <div class="btn brand hover-effect z-depth-1">
                        <span>Upload Photo</span>
                        <input type="file" name="image" accept="image/*">
        <div class="red-text"><?php echo $errors['image'] ?? ''; ?></div>
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="">
                    </div>
                </div>

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
