<?php 
session_start();
include('config/db_connect.php');

// Silme işlemi için POST kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['post_id'], $_POST['category'])) {
    $post_id_to_delete = intval($_POST['post_id']);
    $category_to_delete = $_POST['category'];

    // Önce posts tablosundan ilgili kategori id (f_id, b_id, vs) alalım
    $sql_get_ids = "SELECT * FROM posts WHERE post_id = ?";
    $stmt_ids = $conn->prepare($sql_get_ids);
    $stmt_ids->bind_param("i", $post_id_to_delete);
    $stmt_ids->execute();
    $result_ids = $stmt_ids->get_result();
    if ($result_ids->num_rows === 0) {
        die("Gönderi bulunamadı.");
    }
    $post_ids = $result_ids->fetch_assoc();

    // İlgili kategori tablosundan silme sorgusu
    switch ($category_to_delete) {
        case 'Food Recipes':
            $sql_del_cat = "DELETE FROM foods WHERE p_id = ?";
            $cat_id = $post_ids['f_id'];
            break;
        case 'Book':
            $sql_del_cat = "DELETE FROM book WHERE b_id = ?";
            $cat_id = $post_ids['b_id'];
            break;
        case 'Movie':
            $sql_del_cat = "DELETE FROM movie WHERE m_id = ?";
            $cat_id = $post_ids['m_id'];
            break;
        case 'My Spot':
            $sql_del_cat = "DELETE FROM spot WHERE s_id = ?";
            $cat_id = $post_ids['s_id'];
            break;
        case 'Travel':
            $sql_del_cat = "DELETE FROM travel WHERE t_id = ?";
            $cat_id = $post_ids['t_id'];
            break;
        default:
            die("Bilinmeyen kategori.");
    }

    // Silme işlemleri
    $stmt_del_cat = $conn->prepare($sql_del_cat);
    $stmt_del_cat->bind_param("i", $cat_id);
    $stmt_del_cat->execute();

    $stmt_del_post = $conn->prepare("DELETE FROM posts WHERE post_id = ?");
    $stmt_del_post->bind_param("i", $post_id_to_delete);
    $stmt_del_post->execute();

    header("Location: index.php");
    exit;
}

// GET ile post_id al
if (!isset($_GET['post_id'])) {
    header('Location: index.php');
    exit;
}

$post_id = intval($_GET['post_id']);

// posts tablosundan kategori, kullanıcı ve kategori özel id değerlerini al
$sql_post = "SELECT p.*, c.c_name, u.username, u.email, u.u_role
             FROM posts p
             JOIN category c ON p.c_id = c.c_id
             JOIN users u ON p.u_id = u.u_id
             WHERE p.post_id = ?";
$stmt_post = $conn->prepare($sql_post);
$stmt_post->bind_param("i", $post_id);
$stmt_post->execute();
$result_post = $stmt_post->get_result();

if ($result_post->num_rows === 0) {
    die("Gönderi bulunamadı.");
}

$post = $result_post->fetch_assoc();
$category = $post['c_name'];
$user_id = $post['u_id'];

// Kategoriye göre detay tablo ve id belirleme
switch ($category) {
    case 'Food Recipes':
        $detail_table = 'foods';
        $detail_id_col = 'p_id';
        $detail_id = $post['f_id'];
        break;
    case 'Book':
        $detail_table = 'book';
        $detail_id_col = 'b_id';
        $detail_id = $post['b_id'];
        break;
    case 'Movie':
        $detail_table = 'movie';
        $detail_id_col = 'm_id';
        $detail_id = $post['m_id'];
        break;
    case 'My Spot':
        $detail_table = 'spot';
        $detail_id_col = 's_id';
        $detail_id = $post['s_id'];
        break;
    case 'Travel':
        $detail_table = 'travel';
        $detail_id_col = 't_id';
        $detail_id = $post['t_id'];
        break;
    default:
        die("Bilinmeyen kategori.");
}

// Detay verisini ilgili tabloda al
$sql_detail = "SELECT * FROM $detail_table WHERE $detail_id_col = ?";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $detail_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

if ($result_detail->num_rows === 0) {
    die("Detay bulunamadı.");
}

$detail = $result_detail->fetch_assoc();

?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php') ?>

<div class="container center form-card">

    <h4 style="font-family: 'Fredoka', cursive; color: var(--accent); margin-bottom: 16px; letter-spacing:1px; font-size:2rem;">
        <?php 
        $emoji_map = [
            'Food Recipes' => '🍕',
            'Book' => '📚',
            'Movie' => '🎬',
            'My Spot' => '📍',
            'Travel' => '✈️',
        ];
        echo ($emoji_map[$category] ?? '') . ' ' . htmlspecialchars($detail['title']);
        ?>
    </h4>

    <p style="font-weight: 600; color: var(--text); margin-bottom:2px;">Created by: <?php echo htmlspecialchars($post['username']); ?></p>
    <p style="color:var(--text); font-size: 0.98rem; margin-bottom:2px;">Email: <?php echo htmlspecialchars($post['email']); ?></p>
    <p style="color: var(--text); font-size: 0.92rem; margin-bottom: 20px;"><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($detail['created_at'])); ?></p>

    <?php 
    // İçerik gösterimi kategoriye göre
    switch ($category) {
        case 'Food Recipes':
            echo "<h5 style='font-family: Fredoka, cursive; color: var(--accent); margin-bottom:8px;'>Ingredients:</h5>";
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . htmlspecialchars($detail['ingredients']) . "</p>";
            echo "<h5 style='font-family: Fredoka, cursive; color: var(--accent); margin-bottom:8px;'>Recipe:</h5>";
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . nl2br(htmlspecialchars($detail['recipe'])) . "</p>";
            break;
        case 'Book':
            echo "<h5 style='font-family: Fredoka, cursive; color: var(--accent); margin-bottom:8px;'>Comment:</h5>";
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . nl2br(htmlspecialchars($detail['b_description'])) . "</p>";
            if (!empty($detail['b_image'])) {
                echo "<img src='uploads/" . htmlspecialchars($detail['b_image']) . "' alt='Book Image' style='max-width:100%; margin-bottom: 20px; border-radius:10px;'>";
            }
            break;
        case 'Movie':
            echo "<h5 style='font-family: Fredoka, cursive; color: var(--accent); margin-bottom:8px;'>Comment:</h5>";
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . nl2br(htmlspecialchars($detail['m_description'])) . "</p>";
            if (!empty($detail['m_image'])) {
                echo "<img src='uploads/" . htmlspecialchars($detail['m_image']) . "' alt='Movie Image' style='max-width:100%; margin-bottom: 20px; border-radius:10px;'>";
            }
            break;
        case 'My Spot':
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . nl2br(htmlspecialchars($detail['description'])) . "</p>";
            if (!empty($detail['image'])) {
                echo "<img src='uploads/" . htmlspecialchars($detail['image']) . "' alt='Spot Image' style='max-width:100%; margin-bottom: 20px; border-radius:10px;'>";
            }
            break;
        case 'Travel':
            echo "<p style='color: var(--text); font-size: 1.08rem; margin-bottom:24px;'>" . nl2br(htmlspecialchars($detail['t_description'])) . "</p>";
            if (!empty($detail['t_image'])) {
                echo "<img src='uploads/" . htmlspecialchars($detail['t_image']) . "' alt='Travel Image' style='max-width:100%; margin-bottom: 20px; border-radius:10px;'>";
            }
            break;
    }
    ?>

    <?php if (
        (isset($_SESSION['u_role']) && $_SESSION['u_role'] == 'admin')
        || (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $user_id)
    ) : ?>
        <form action="details.php" method="POST" style="margin-top: 20px;">
            <input type="hidden" name="id_to_delete" value="<?php echo $post_id; ?>">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <input type="submit" name="delete" value="Delete" class="btn brand z-depth-1" style="border:none; border-radius: 24px; padding: 0 32px; font-weight:600; letter-spacing:1px; background: linear-gradient(90deg, var(--accent), var(--mint));">
        </form>
    <?php endif; ?>

</div>

<?php include('templates/footer.php') ?>

</html>
