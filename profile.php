<?php 
session_start();
include('config/db_connect.php');

if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

$user_id = isset($_GET['u_id']) ? intval($_GET['u_id']) : $_SESSION['u_id'];

// Kullanıcı bilgileri
$sql_user = "SELECT username, email, gender, u_role, profile_image, about FROM users WHERE u_id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

$user_posts = [];

// Silme işlemi için POST kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['post_id'], $_POST['category'])) {
    $post_id_to_delete = intval($_POST['post_id']);
    $category_to_delete = $_POST['category'];

    switch ($category_to_delete) {
        case 'Food Recipes':
            $sql_del = "DELETE FROM foods WHERE f_id = ?";
            break;
        case 'Book':
            $sql_del = "DELETE FROM book WHERE b_id = ?";
            break;
        case 'Movie':
            $sql_del = "DELETE FROM movie WHERE m_id = ?";
            break;
        case 'My Spot':
            $sql_del = "DELETE FROM spot WHERE s_id = ?";
            break;
        case 'Travel':
            $sql_del = "DELETE FROM travel WHERE t_id = ?";
            break;
        default:
            die("Bilinmeyen kategori.");
    }

    $stmt = $conn->prepare($sql_del);
    if ($stmt === false) {
        die("SQL prepare hatası: " . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $post_id_to_delete);
    $stmt->execute();

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Foods
$sql_foods = "SELECT f_id AS post_id, 'Food Recipes' AS category, title, ingredients, NULL AS comment, NULL AS image, created_at, u_id
              FROM foods 
              WHERE u_id = ?";
$stmt = $conn->prepare($sql_foods);
if (!$stmt) {
    die("Prepare hatası: " . $conn->error);
}
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Books
$sql_books = "SELECT b_id AS post_id, 'Book' AS category, title, NULL AS ingredients, b_description AS comment, b_image AS image, created_at, u_id
              FROM book
              WHERE u_id = ?";
$stmt = $conn->prepare($sql_books);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Movies
$sql_movies = "SELECT m_id AS post_id, 'Movie' AS category, title, NULL AS ingredients, m_description AS comment, m_image AS image, created_at, u_id
               FROM movie
               WHERE u_id = ?";
$stmt = $conn->prepare($sql_movies);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Spots
$sql_spots = "SELECT s_id AS post_id, 'My Spot' AS category, title, NULL AS ingredients, description AS comment, image AS image, created_at, u_id
              FROM spot
              WHERE u_id = ?";
$stmt = $conn->prepare($sql_spots);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

// Travels
$sql_travel = "SELECT t_id AS post_id, 'Travel' AS category, title, NULL AS ingredients, t_description AS comment, t_image AS image, created_at, u_id
               FROM travel
               WHERE u_id = ?";
$stmt = $conn->prepare($sql_travel);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_posts[] = $row;
}
$stmt->close();

$conn->close();

// Postları created_at'a göre azalan sırala (yeni paylaşımlar üstte)
usort($user_posts, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});


function formatGender($gender) {
    switch($gender) {
        case 'male': return 'Male';
        case 'female': return 'Female';
        case 'other': return 'Other';
        case 'prefer_not_say': return 'Prefer not to say';
        default: return ucfirst($gender);
    }
}

function formatRole($u_role) {
    switch($u_role) {
        case 'admin': return 'Admin';
        case 'user': return 'User';
        default: return ucfirst($u_role);
    }
}
?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php') ?>

<h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;"> Your Cozy Shares</h4>

<aside class="profile-card">
            <div class="right-align link" style="margin-bottom: 10px;">
                <?php if (isset($_SESSION['u_id']) && $_SESSION['u_id'] == $user_id): ?>
                    <a href="edit.php" class="text btn-small btn brand hover-effect z-depth-1 " style="font-weight:600;">
                        Edit Profile <i class="fa-solid fa-pen"></i>
                    </a>
                <?php endif; ?>
            </div>
            <h2 class="text" style="margin-bottom: 12px;">Profile</h2>
            <div class="profile-card-img-container">
                <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-card-img">
            </div>
            <p class="text" style="font-weight:600;"><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p class="text" style="font-weight:600;"><strong>Role:</strong> <?php echo formatRole($user['u_role']); ?></p>
            <p class="text" style="font-weight:600;"><strong>Email:</strong> </p>
            <p class="text" style="font-weight:600;"><strong>Gender:</strong> <?php echo formatGender($user['gender']); ?></p>
            <p class="text" style="font-weight:600;"><strong>About Me</strong></p>
            <p class="text left-align">
                <?php echo htmlspecialchars($user['about']); ?>
            </p>
</aside>
    
<div class="container">
    <div>
        <?php if (count($user_posts) > 0): ?>
            <?php foreach ($user_posts as $post): ?>
                <div class="card hover-effect z-depth-1" style="margin-bottom:32px; margin-top: 42px;">
                    <div class="center" style="margin: 10px 8px;">
                        <h5 class="text" style="margin:10px 0 8px 0; font-size:1.2rem; letter-spacing:0.5px;">
                            <?php 
                                switch($post['category']){
                                    case 'Food Recipes': echo "🍕 "; break;
                                    case 'Book': echo "📚 "; break;
                                    case 'Movie': echo "🎬 "; break;
                                    case 'My Spot': echo "📍 "; break;
                                    case 'Travel': echo "✈️ "; break;
                                }
                                echo htmlspecialchars($post['title']);
                            ?>
                        </h5>
                        <?php if(!empty($post['ingredients'])): ?>
                            <ul class="text" style="font-size:1rem; margin-left:0; padding-left:1rem;">
                                <?php foreach(explode(',', $post['ingredients']) as $ing): ?>
                                    <li><?php echo htmlspecialchars(trim($ing)); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif(!empty($post['comment'])): ?>
                        <p class="text" style="font-size:1rem;">
                            <?php echo htmlspecialchars(mb_strimwidth($post['comment'], 0, 200, '...')); ?>
                        </p>
                        <?php if(!empty($post['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="" style="max-width:90%; margin-top:12px; border-radius: 10px;">
                        <?php endif; ?>
                        <?php endif; ?>
                        <div class="card-action right-align" style="margin-top:12px;">
                            <small class="text" style="position: absolute; left:20px; bottom:20px; font-size: 14px;">
                                <?php echo date('d M Y', strtotime($post['created_at'] ?? '')); ?>
                            </small>
                            <?php if ($_SESSION['u_id'] == $post['u_id']): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($post['category']); ?>">
                                    <button type="submit" name="delete"  class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--accent); font-weight:600;">Nothing posted yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include('templates/footer.php') ?>

</html>
