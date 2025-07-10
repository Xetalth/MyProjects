<?php
session_start();
include('config/db_connect.php');

// Silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'], $_POST['post_id'], $_POST['type'])) {
    $post_id_to_delete = intval($_POST['post_id']);
    $type_to_delete = $_POST['type'];

    switch ($type_to_delete) {
        case 'food':
            $sql_del = "DELETE FROM foods WHERE f_id = ?";
            break;
        case 'book':
            $sql_del = "DELETE FROM book WHERE b_id = ?";
            break;
        case 'movie':
            $sql_del = "DELETE FROM movie WHERE m_id = ?";
            break;
        case 'travel':
            $sql_del = "DELETE FROM travel WHERE t_id = ?";
            break;
        case 'spot':
            $sql_del = "DELETE FROM spot WHERE s_id = ?";
            break;
        default:
            die("Bilinmeyen paylaşım türü.");
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

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$where = [];
if ($category_id > 0) {
    $where[] = "all_posts.c_id = $category_id";
}
if ($search !== '') {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $where[] = "all_posts.title LIKE '%$search_escaped%'";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";


$sql = "
SELECT all_posts.*, users.username 
FROM (
  (SELECT f_id AS id, c_id, title, ingredients AS description, NULL AS image, u_id, created_at, 'food' AS type FROM foods)
  UNION ALL
  (SELECT b_id AS id, c_id, title, b_description AS description, b_image AS image, u_id, created_at, 'book' AS type FROM book)
  UNION ALL
  (SELECT m_id AS id, c_id, title, m_description AS description, m_image AS image, u_id, created_at, 'movie' AS type FROM movie)
  UNION ALL
  (SELECT t_id AS id, c_id, title, t_description AS description, t_image AS image, u_id, created_at, 'travel' AS type FROM travel)
  UNION ALL
  (SELECT s_id AS id, c_id, title, description, image, u_id, created_at, 'spot' AS type FROM spot)
) AS all_posts
JOIN users ON all_posts.u_id = users.u_id
$where_sql
ORDER BY all_posts.created_at DESC
";


$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query error: " . mysqli_error($conn));
}

$posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php') ?>



<h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Posts</h4>

<aside class="filter">
        <form class="z-depth-1 filter-card " method="GET" action="index.php">
            <h5 class="text">Filters</h5>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="0">All Posts</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="1">🍕 Food</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="2">✈️ Vacation/View</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="3">📚 Book</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="4">🎬 Film & Series</button>
            <button class="btn-small hover-effect brand btn" type="submit" name="category_id" value="5">📍 My Spot</button>
            <input class="search-bar" type="text" name="search" placeholder="Search...">
            <button class="btn-small hover-effect brand btn" type="submit">Ara</button>
            <?php if ($category_id > 0): ?>
            <p style="text-align:center; font-weight:bold; color: var(--accent);">
                Results for: 
                <?php
                    $kategori_etiket = [
                        1 => '🍕 Food',
                        2 => '✈️ Vacation/View',
                        3 => '📚 Book',
                        4 => '🎬 Movie & Series',
                        5 => '📍 My Spot',
                    ];
                    echo $kategori_etiket[$category_id] ?? 'Bilinmeyen';
                ?>
            </p>
        <?php endif; ?>
        </form>

    </aside>

<div class="container"> 
    <!-- POST ALANI -->
    <div style="flex: 1; max-width: 650px;">
        <?php foreach($posts as $post): ?>
            <!-- Mevcut post kartların burada kalacak -->
        <?php endforeach; ?>
    </div>
    <?php foreach($posts as $post): ?>
        <div class="card hover-effect z-depth-1">
            <div class="center" style="margin: 10px 8px;" >
                <a class="btn-small btn hover-effect brand z-depth-1" href="profile.php?u_id=<?php echo $post['u_id']; ?>">
                    <?php echo htmlspecialchars($post['username']); ?> <i class="fa-regular fa-user" style="margin-left:3px; font-size:0.95em;"></i>
                </a>
                <h6 class="text">
                    <?php 
                        $emoji_map = [
                            'food' => '🍕',
                            'book' => '📚',
                            'movie' => '🎬',
                            'travel' => '✈️',
                            'spot' => '📍',
                        ];
                        echo ($emoji_map[$post['type']] ?? '') . ' ' . htmlspecialchars($post['title']); 
                    ?>
                </h6>
                
                <?php if ($post['type'] === 'food'): ?>
                    <ul class="text">
                        <?php foreach(explode(',', $post['description']) as $ing): ?>
                            <li style="margin-bottom:2px;"><?php echo htmlspecialchars(trim($ing)); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                <?php endif; ?>

                <?php if (!empty($post['image'])): ?>
                    <div style="margin-top:8px;">
                        <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="max-width:90%; border-radius: 10px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-action" style="margin-top:12px; display: flex; justify-content: space-between; align-items: center;">
                <small class="text" style=" left:20px; bottom:20px; font-size: 14px;">
                    <?php echo date('d M Y', strtotime($post['created_at'] ?? '')); ?>
                </small>

                <?php if (isset($_SESSION['u_role']) && $_SESSION['u_role'] === 'admin'): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="hidden" name="type" value="<?php echo htmlspecialchars($post['type']); ?>">
                        <button type="submit" name="delete"  class="btn-small hover-effect btn brand" onclick="return confirm('Are you sure you want to delete?');">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include('templates/footer.php') ?>

</html>
