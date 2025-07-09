<?php
    session_start();
    include('config/db_connect.php');

    if (!isset($_SESSION['u_id'])) {
    header('Location: log_in.php');
    exit;
}

        $id = $_SESSION['u_id'];

        $sql = "SELECT username, email, gender, profile_image, about FROM users WHERE u_id = $id";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        $username = $user['username'];
        $email = $user['email'];
        $gender = $user['gender'];
        $profileImage = $user['profile_image'];
        $about = $user['about'];

        if (isset($_POST['update'])) {
            $new_username = mysqli_real_escape_string($conn, $_POST['username']);
            $new_email = mysqli_real_escape_string($conn, $_POST['email']);
            $new_gender = mysqli_real_escape_string($conn, $_POST['gender']);
            $new_about = mysqli_real_escape_string($conn, $_POST['about']);
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $img_tmp = $_FILES['profile_image']['tmp_name'];
                $img_name = basename($_FILES['profile_image']['name']);
                $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];    
                
                if (in_array($img_ext, $allowed_ext)) {
                    $new_img_name = $id .uniqid('', true). '.' . $img_ext;
                    $fileDestination = 'uploads/'. $new_img_name;
                    move_uploaded_file($img_tmp, $fileDestination);
                    
                    if(!empty($profileImage)){
                        $old_file_path = 'uploads/' . $profileImage;
                        if (file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }

                    $profile_image = $new_img_name;

                    $update_sql = "UPDATE users SET 
                        username = '$new_username',
                        email = '$new_email',
                        gender = '$new_gender', 
                        profile_image = '$profile_image',
                        about = '$new_about'
                        WHERE u_id = $id";

                } else {
                    echo "Geçersiz dosya türü. Sadece JPG, PNG, GIF kabul edilir.";
                    exit;
                }
            } else {
        
                $update_sql = "UPDATE users SET 
                    username = '$new_username',
                    email = '$new_email',
                    gender = '$new_gender',
                    about = '$new_about'
                    WHERE u_id = $id";
            }
            if (mysqli_query($conn, $update_sql)) {
                header("Location: profile.php?success=1");
                exit;
            } else {
                echo "Hata: " . mysqli_error($conn);
        }
    }

?>

<!DOCTYPE html>
<html>

<?php include('templates/header.php'); ?>
<?php //  // Close the database connection style="padding: 40px 32px; border-radius: 18px; max-width: 480px; margin: 48px auto; background: #fffaf6; box-shadow: 0 4px 16px rgba(255,180,162,0.08);"?>
<div class="container">
    
        <div class="card-content center">
            <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Edit Profile</h4>

            <?php if (isset($_GET['success'])): ?>
                <div style="color: green; margin: 10px 0; font-weight:600;">Profile updated successfully!</div>
            <?php endif; ?>

            <div class="profile-card-img-container">
            <?php if (isset($profileImage) && $profileImage !== ''): ?>
                <img src="uploads/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-card-img" style="width:240px; height:240px;">
            <?php else: ?>
                <i class="fa fa-user-circle fa-7x" aria-hidden="true"></i>
            <?php endif; ?>
            </div>

            <form class="form-card z-depth-1" action="edit.php" method="POST" enctype="multipart/form-data">
                <div class="form-card-content">
                    <div class="input-field">
                    <label for="username" style="font-weight:600;" class="text" >Username</label>
                    <input class="text" type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>

                <div class="input-field">
                    <label for="email" style="font-weight:600;" class="text" >Email</label>
                    <input class="text" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>

                <div class="input-field">
                    <label for="about" style="font-weight:600;" class="text">About Me</label>
                    <textarea id="about" name="about" class="text materialize-textarea"><?php echo htmlspecialchars($about); ?></textarea>
                </div>

                <div class="input-field ">
                    <label for="gender"></label>
                    <select  name="gender">
                        <option value="" disabled <?php if(empty($gender)) echo 'selected'; ?>>Select gender</option>
                        <option value="male" <?php if(isset($gender) && $gender == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if(isset($gender) && $gender == 'female') echo 'selected'; ?>>Female</option>
                        <option value="other" <?php if(isset($gender) && $gender == 'other') echo 'selected'; ?>>Other</option>
                        <option value="prefer_not_say" <?php if(isset($gender) && $gender == 'prefer_not_say') echo 'selected'; ?>>Prefer not to say</option>
                    </select>
                </div>

                <div class="file-field input-field">
                    <div class="btn-small btn hover-effect brand z-depth-1">
                        <span>Upload Photo</span>
                        <input type="file" name="profile_image" accept="image/*">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="text file-path validate" type="text" placeholder="">
                    </div>
                </div>

                <div style="margin-top: 20px; display: flex; align-items: center;">
                    <input type="submit" name="update" value="Update" class="btn hover-effect brand z-depth-1" >
                    <a href="change_password.php" style="margin-left: auto; font-weight:600; color: var(--accent);" class="hover-effect" >Change Password</a>
                </div>
                </div>
            </form>
        </div>
</div>

<?php include('templates/footer.php'); ?>

</html>

   
