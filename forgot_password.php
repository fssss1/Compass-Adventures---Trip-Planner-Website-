<?php

require 'dbConnect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


date_default_timezone_set('Asia/Manila');

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {

            $token = bin2hex(random_bytes(32)); 
            $otp = random_int(100000, 999999);  
            $otpExpiry = date('Y-m-d H:i:s', time() + (60 * 10)); 
            $passwordSetToken = $token; 
            $passwordSetExpiry = date('Y-m-d H:i:s', time() + (60 * 60)); 

            echo "Generated Token: " . $token . "<br>";
            echo "OTP: " . $otp . "<br>";
            echo "OTP Expiry: " . $otpExpiry . "<br>";
            echo "Password Set Expiry: " . $passwordSetExpiry . "<br>";

            $stmt = $pdo->prepare("UPDATE users 
                SET 
                    reset_token = ?, 
                    reset_token_expiry = ?, 
                    reset_otp = ?, 
                    password_set_token = ?, 
                    password_set_expiry = ? 
                WHERE id = ?");
            $stmt->execute([
                $token,
                $otpExpiry,
                $otp,
                $passwordSetToken,
                $passwordSetExpiry,
                $user['id']
            ]);

            $subject = "Password Reset OTP";
            $body = "Your One-Time Password (OTP) for password reset is: <b>$otp</b><br><br>This OTP will expire in 10 minutes.<br><br>If you didn't request this, ignore the email.";
            $altBody = "Your OTP for password reset is: $otp\nExpires in 10 minutes.";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '2021-105013@rtu.edu.ph';
                $mail->Password = 'vfjt ftep iaih mutr'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('thenolangrayson1@gmail.com', 'Group Six Compass');
                $mail->addAddress($email, $user['username']);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->AltBody = $altBody;

                $mail->send();
                $success_message = 'An OTP has been sent to your email. Please check and verify it.';

                header("Location: verify_otp.php?email=" . urlencode($email));
                exit();

            } catch (Exception $e) {
                $errors[] = "Email could not be sent. Error: {$mail->ErrorInfo}";
            }
        } else {
            $errors[] = 'There is no user with that email address.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1 class="upperMessage">Forgot Your Password?</h1>
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success-message">
            <p><?= htmlspecialchars($success_message) ?></p>
        </div>
    <?php endif; ?>

    <div class="container">
        <form method="post">
            <div class="input-wrapper">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Your Email Address" required>
            </div>
            <input type="submit" class="btn" value="Request OTP">
        </form>
        <div class="links">
            <p>Back to <a href="login.php">Log in</a></p>
        </div>
    </div>
    <img src="forget.svg" alt="Forgot Password">
</body>
</html>





