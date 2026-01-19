<?php
session_start();
require_once 'dbConnect.php';

$errors = [];
$email = "";

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $errors[] = 'Account locked due to too many failed attempts. Please try again after 1 hour.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // ✅ Set both username and user ID in session
            $_SESSION['user'] = $user['username'];
            $_SESSION['user_id'] = $user['id']; // Adjust this if your column name is different
            $_SESSION['attempts'] = 0;
            unset($_SESSION['lockout_time']);
            header('Location: CompassHome.html');
            exit();
        } else {
            $_SESSION['attempts']++;
            if ($_SESSION['attempts'] >= 3) {
                $_SESSION['lockout_time'] = time() + (60 * 60);
                $errors[] = 'Too many failed login attempts. Please wait 1 hour before trying again.';
            } else {
                $remaining = 3 - $_SESSION['attempts'];
                $errors[] = "Invalid email or password. You have $remaining attempt(s) left.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Form</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<h1 class="upperMessage">Good to see you again</h1>

<div class="container">
  <h1 class="form-title">Log in</h1>

  <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
    <div class="success-message">
      <p>Sign up successful! Please log in.</p>
    </div>
  <?php endif; ?>

  <?php if (isset($_GET['verification_success'])): ?>
    <div class="success-message">
      <p>Your email has been successfully verified! You can now log in.</p>
    </div>
  <?php endif; ?>

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
      <i class="fas fa-envelope"></i>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email" required>
    </div>

    <div class="input-wrapper">
      <i class="fas fa-lock"></i>
      <input type="password" id="password" name="password" placeholder="Password" required>
      <i class="fas fa-eye toggle-password" id="togglePassword" style="cursor: pointer;"></i>
    </div>

    <input type="submit" class="btn" value="Log in">
  </form>

  <div class="links">
    <p>Don't have an account yet? <a href="signup.php">Sign up</a></p>
    <p class="forgot-password"><a href="forgot_password.php">Forgot your password?</a></p>
  </div>
</div>

<img src="Pictures/logpic.svg" alt="">

<script>
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', function () {
  const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', type);
  this.classList.toggle('fa-eye');
  this.classList.toggle('fa-eye-slash');
});
</script>

</body>
</html>

