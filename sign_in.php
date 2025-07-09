<?php
    session_start();       
    include('config/db_connect.php');

    $email = $password = $username = $gender = '';
    
    $errors = array('username' => '', 'email' => '', 'password' => '', 'gender' => '');
    if(isset($_POST['submit'])) {

        //check Username
        if(empty($_POST['username'])){
            $errors['username'] = 'A Username is required <br />';
        } else {
            $username = $_POST['username'];
            if(!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9_-]{1,18})[a-zA-Z0-9]$/', $username)){
                $errors['username'] = 'Invalid username. 3-20 characters, must start and end with letter/number, only _ and - allowed. <br />';
            }
        }

        //check email
        if(empty($_POST['email'])){
            $errors['email'] = 'An email is required <br />';
        } else {
           $email = $_POST['email'];
            if(!filter_var($email,  FILTER_VALIDATE_EMAIL)){
                $errors['email'] = 'Email must be a valid email address <br />';
            } else {
                //check if email already exists
                $sql = "SELECT * FROM users WHERE email = '$email'";
                $result = mysqli_query($conn, $sql);
                if(mysqli_num_rows($result) > 0){
                    $errors['email'] = 'Email already exists <br />';
                }
            }
        }

        //check password
        if(empty($_POST['password'])){
            $errors['password'] = 'An password is required <br />';
        } else {
            $password = $_POST['password'];
            if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,}$/', $password)){
                $errors['password'] = 'Password must be at least 8 characters and include uppercase letters, lowercase letters, numbers and special characters.<br />';
            }
        }    
        
        //check gender
        if(empty($_POST['gender'])){
            $errors['gender'] = 'A gender is required <br />'; 

        }
        
        if(array_filter($errors)) {
           //echo 'errors in the form'; 
        } else{

            $username = mysqli_real_escape_string($conn, $_POST['username']);
            $email = mysqli_real_escape_string($conn, $_POST['email']);
            $password = mysqli_real_escape_string($conn, $_POST['password']);
            $gender = mysqli_real_escape_string($conn, $_POST['gender']);

            //hash password
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            //create sql
            $sql = "INSERT INTO users(username, email, password, gender) VALUES ('$username', '$email', '$password','$gender')";

            //save to db and check
            if(mysqli_query($conn, $sql)){
                //success
                header('Location: log_in.php');
            } else{   
                echo 'query error:  '. mysqli_error($conn);
            }    
            
            
        } 


    }// end of POST check    
?>


<!DOCTYPE html>
<html>

 <?php include('templates/header.php') ?>

    

    <section class="container">
        <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Sign In</h4>
        <form class="z-depth-1 form-card " action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST">
            <div class="form-card-content">
                <label class="text" style="font-weight:600;">Username:</label>
                <input class="text" type="text" name="username" autocomplete="off" value ="<?php echo htmlspecialchars($username); ?>">
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['username']; ?></div>
                <label class="text" style="font-weight:600;">Email:</label>
                <input class="text" type="text" name="email" autocomplete="off" value ="<?php echo htmlspecialchars($email); ?>">
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['email']; ?></div>
                <label class="text" style="font-weight:600;">Password</label>
                <input class="text" type="password" autocomplete="off" name="password" value ="<?php echo htmlspecialchars($password); ?>">
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['password']; ?></div>
                <label class="text" style="font-weight:600; color: var(--text);">Gender</label>
                <select name="gender">
                    <option value="" disabled <?php if(empty($gender)) echo 'selected'; ?>>Select gender</option>
                    <option value="male" <?php if(isset($gender) && $gender == 'male') echo 'selected'; ?>>Male</option>
                    <option value="female" <?php if(isset($gender) && $gender == 'female') echo 'selected'; ?>>Female</option>
                    <option value="other" <?php if(isset($gender) && $gender == 'other') echo 'selected'; ?>>Other</option>
                    <option value="prefer_not_say" <?php if(isset($gender) && $gender == 'prefer_not_say') echo 'selected'; ?>>Prefer not to say</option>
                </select>
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['gender']; ?></div>
                <div class="center">
                    <input type="submit" name="submit" value="Sign In"  class="btn hover-effect brand z-depth-1">
                </div>
            </div>
        </form>
        <div class="center" style="margin-top:16px;">
            Already have an account? <a href="log_in.php" class="hover-effect" style="color:var(--accent); font-weight:600;">Log in now!</a>
        </div>
    </section>



 <?php include('templates/footer.php') ?>

</html>