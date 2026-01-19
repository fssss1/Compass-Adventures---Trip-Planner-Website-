<?php
session_start();
require_once 'dbConnect.php';

$errors = [];
$username = "";
$email = "";
$gender = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $gender = $_POST['gender'] ?? '';

    if (empty($username)) {
        $errors[] = 'Username is required';
    }
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    if (empty($confirmPassword)) {
        $errors[] = 'Confirm Password is required';
    }
    if (empty($gender)) {
        $errors[] = 'Gender is required';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must include at least one uppercase letter';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must include at least one number';
    }
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = 'Password must include at least one special character';
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existingUser) {
        $errors[] = 'Email already registered';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, gender) VALUES (:username, :email, :password, :gender)");
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'gender' => $gender
        ]);
        header('Location: login.php?signup=success');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up Form</title>
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>
    <h1 class="upperMessage">Join in and sign up</h1>
<div class="container">
    <h1 class="form-title">Sign Up</h1>

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
            <i class="fas fa-user"></i>
            <input type="text" name="user" value="<?= htmlspecialchars($username) ?>" placeholder="Username" required>
        </div>

        <div class="input-wrapper">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
        </div>

        <div class="input-wrapper">
    <i class="fas fa-lock"></i>
    <input type="password" id="password" name="password" placeholder="Password" required>
    <i class="fas fa-eye toggle-password" id="togglePassword" style="cursor: pointer;"></i>
</div>

<div class="input-wrapper">
    <i class="fas fa-lock"></i>
    <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
    <i class="fas fa-eye toggle-password" id="toggleConfirmPassword" style="cursor: pointer;"></i>
</div>


        <div class="input-wrapper">
            <i class="fas fa-venus-mars"></i>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male" <?= $gender === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $gender === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $gender === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="formMessage">
            <p>Read and agree with the terms and conditions for further understandiing of security and liabilities</p>
        </div>
        
        <div class="agreementForm">
            <label for="agreementForm"><a href="termsCondition.html">Terms and Conditions</a></label>
            <input type="checkbox" name="agreementForm" required>
        </div>

        <input type="submit" class="btn" value="Sign Up">
    </form>
    <div class="links">
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</div>
<img src="Pictures/sign.svg" alt="">

<script>
    function togglePasswordVisibility(toggleId, inputId) {
        const toggleIcon = document.getElementById(toggleId);
        const passwordInput = document.getElementById(inputId);

        toggleIcon.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    togglePasswordVisibility('togglePassword', 'password');
    togglePasswordVisibility('toggleConfirmPassword', 'confirmPassword');
</script>

</body>
</html>
