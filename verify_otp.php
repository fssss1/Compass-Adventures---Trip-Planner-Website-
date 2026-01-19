<?php

require 'dbConnect.php';

date_default_timezone_set('Asia/Manila');

$errors = [];
$success_message = '';

$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);

if (empty($email)) {
    $errors[] = 'Email address is missing. Please return to the forgot password page and try again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_input = filter_input(INPUT_POST, 'otp', FILTER_SANITIZE_NUMBER_INT);

    if (empty($otp_input)) {
        $errors[] = 'Please enter the OTP sent to your email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, reset_otp, reset_token_expiry, password_set_token, password_set_expiry FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $stored_otp = $user['reset_otp']; 
            $otp_expiry_time = $user['reset_token_expiry']; 

            if (time() > strtotime($otp_expiry_time)) {
                $errors[] = 'The OTP has expired. Please request a new password reset.';
            } elseif ($otp_input != $stored_otp) {
                $errors[] = 'Invalid OTP. Please enter the correct OTP from your email.';
            } else {
                $password_set_token = bin2hex(random_bytes(32));     
                $update_stmt = $pdo->prepare("UPDATE users SET 
                                                password_set_token = :password_set_token, 
                                                password_set_expiry = DATE_ADD(NOW(), INTERVAL 30 MINUTE), 
                                                reset_otp = NULL, 
                                                reset_token = NULL, 
                                                reset_token_expiry = NULL 
                                              WHERE id = :id");
                $update_stmt->execute([
                    ':password_set_token' => $password_set_token,
                    ':id' => $user['id']
                ]);
                header("Location: set_new_password.php?token=" . urlencode($password_set_token));
                exit();
            }
        } else {
            $errors[] = 'Invalid email address. Please return to the forgot password page.'; 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1 class="upperMessage">Verify OTP</h1>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="container">
        <form method="post">
            <p>Please enter the One-Time Password (OTP) sent to your email address.</p>
            <div class="input-wrapper">
                <i class="fas fa-key"></i>
                <input type="text" name="otp" placeholder="Enter OTP" required>
            </div>
            <input type="submit" class="btn" value="Verify OTP">
        </form>
        <div class="links">
            <p>Back to <a href="forgot_password.php">Forgot Password</a></p>
            <p>Back to <a href="login.php">Log in</a></p>
        </div>
    </div>
    <img src="verify_otp.svg" alt="Verify OTP">
</body>
</html>


