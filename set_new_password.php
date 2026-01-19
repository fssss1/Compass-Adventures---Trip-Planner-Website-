<?php

require 'dbConnect.php';

date_default_timezone_set('Asia/Manila'); 

$errors = [];
$user = null;
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (empty($token)) {
    $errors[] = 'Invalid reset token.';
} else {
 
    $stmt = $pdo->prepare("SELECT id AS user_id, email, password_set_expiry FROM users WHERE password_set_token = ? AND password_set_expiry > NOW()");
    if ($stmt) { 
        $stmt->execute([$token]);
       
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = 'Invalid or expired reset token.';
        }
    } else {
        $errors[] = 'Failed to prepare statement to select user data.'; // Add this error
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($user) {
        $password = filter_input(INPUT_POST, 'password');
        $confirmPassword = filter_input(INPUT_POST, 'confirmPassword');

        if (empty($password)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'New password must be at least 8 characters long.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password = ?, password_set_token = NULL, password_set_expiry = NULL WHERE id = ?");
            if ($stmt) { 
                if ($stmt->execute([$hashedPassword, $user['user_id']])) {
                    header("Location: login.php?reset_success=1");
                    exit();
                } else {
                    $errors[] = 'There was an error updating your password. Please try again later.';
                }
            } else {
                 $errors[] = 'Failed to prepare statement to update password.'; // Add this error
            }
        }
    } else {
        $errors[] = 'Invalid or expired reset token.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <h1 class="upperMessage">Reset Your Password</h1>
    <?php if (!empty($errors) && !$user): ?>
        <div class="errors" style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); width: 500px; max-width: 90%;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="container">
        <h1 class="form-title">Reset Your Password</h1>
        <?php if ($user): ?>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="New Password" required autocomplete="new-password">
                    <i class="fas fa-eye toggle-password" id="togglePassword" style="cursor: pointer;"></i>
                </div>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm New Password" required autocomplete="new-password">
                    <i class="fas fa-eye toggle-password" id="toggleConfirmPassword" style="cursor: pointer;"></i>
                </div>
                <input type="submit" class="btn" value="Reset Password">
            </form>
            <div class="links">
                <p>Back to <a href="login.php">Log in</a></p>
            </div>
        <?php else: ?>
            <div class="errors">
                <p>Invalid or expired reset token.</p>
            </div>
             <div class="links">
                <p>Back to <a href="forgot_password.php">Request New Reset Link</a></p>
                <p>Back to <a href="login.php">Log in</a></p>
            </div>
        <?php endif; ?>
    </div>
    <img src="forget.svg" alt="Login Image">
    <script>
        function togglePasswordVisibility(toggleId, inputId) {
            const toggleIcon = document.getElementById(toggleId);
            const passwordInput = document.getElementById(inputId);

            if (toggleIcon && passwordInput) {
                toggleIcon.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    this.classList.toggle('fa-eye');
                    this.classList.toggle('fa-eye-slash');
                });
            }
        }

        togglePasswordVisibility('togglePassword', 'password');
        togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');
    </script>
</body>
</html>







