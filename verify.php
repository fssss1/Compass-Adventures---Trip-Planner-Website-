<?php
require_once 'dbConnect.php';

$errors = [];
$code = $_GET['code'] ?? '';
$email = $_GET['email'] ?? '';

if (empty($code) || empty($email)) {
    $errors[] = 'Invalid verification link.';
}

if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND activation_code = :code");
    $stmt->execute(['email' => $email, 'code' => $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['is_verified']) {
            $errors[] = "Email already verified.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, activation_code = NULL WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $_SESSION['verification_success'] = true;
            header('Location: login.php?verification_success=1');
            exit();
        }
    } else {
        $errors[] = 'Invalid verification link.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Verify Email</h1>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Your email has been successfully verified. You can now <a href="login.php">log in</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>
