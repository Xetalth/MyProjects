<?php
session_start();
include('config/db_connect.php');

$errors = ['email' => '', 'new_password' => '', 'confirm_password' => '', 'general' => ''];
$success = '';

if (isset($_POST['change_password'])) {
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Email kontrolü
    if (empty($email)) {
        $errors['email'] = 'Please enter your email.';
    }

    // Yeni şifre kontrolleri
    if (empty($new_password)) {
        $errors['new_password'] = 'Please enter a new password.';
    }

    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = 'New passwords do not match.';
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $new_password)) {
        $errors['new_password'] = 'Password must be at least 8 characters and include uppercase, lowercase, number and special character.';
    }

    if (!array_filter($errors)) {
        // Kullanıcıyı e-postaya göre bul
        $sql = "SELECT u_id, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $user = $result->fetch_assoc()) {
            $user_id = $user['u_id'];

            // Eski ve yeni şifre aynı mı?
            if (password_verify($new_password, $user['password'])) {
                $errors['new_password'] = 'New password cannot be the same as the old password.';
            } else {
                // Şifreyi güncelle
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE users SET password = ? WHERE u_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $hashed, $user_id);

                if ($update_stmt->execute()) {
                    session_unset();
                    session_destroy();
                    header('Location: log_in.php');
                    exit();
                } else {
                    $errors['general'] = 'Failed to update password. Try again.';
                }
            }
        } else {
            $errors['email'] = 'No account found with that email.';
        }
    }
}
?>          

<!DOCTYPE html>
<html>
<?php include('templates/header.php'); ?>

<section class="container grey-text">
    <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Change Password</h4>
    <form class=" z-depth-1  form-card"  >
        <div class="form-card-content">
            <?php if ($success): ?>
                <p class="green-text" style="font-weight:600;"><?php echo htmlspecialchars($success); ?></p>
            <?php endif; ?>

            <?php if ($errors['general']): ?>
                <p class="red-text" style="font-weight:600;"><?php echo htmlspecialchars($errors['general']); ?></p>
            <?php endif; ?>
                
        
            <label class="text" for="email" class="label">Email Address</label>
            <input class="text" type="email" name="email" id="email" required autocomplete="off">
            <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['email']; ?></div>

            <label class="text" for="new_password" class="label">New Password</label>
            <input class="text" type="password" name="new_password" id="new_password" required>
            <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['new_password']; ?></div>

            <label class="text" for="confirm_password" class="label">Confirm New Password</label>
            <input class="text" type="password" name="confirm_password" id="confirm_password" required>
            <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['confirm_password']; ?></div>
            <div class="center">
                <input type="submit" name="change_password" value="Change Password" class="btn hover-effect brand z-depth-1 " >
            </div>
        </div>
    </form>
</section>


<?php include('templates/footer.php'); ?>
</html>
