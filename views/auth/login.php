<?php
$title = 'Corporate Access - TANTRA GROUP OF INDUSTRIES';
require_once __DIR__ . '/../../middleware/Auth.php';
$auth = Auth::getInstance();
$auth->redirectIfAuthenticated();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $result = $auth->login($username, $password);
        if ($result['success']) {
            $redirect = $_SESSION['redirect_after_login'] ?? '/corporate/dashboard';
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <a href="/" class="login-logo">
                    <span class="logo-icon">T</span>
                    <div class="logo-text">
                        <span class="logo-title">TANTRA</span>
                        <span class="logo-subtitle">CORPORATE ACCESS</span>
                    </div>
                </a>
                <p class="login-subtitle">Authorized personnel only</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/corporate/login" class="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your username" autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">Sign In</button>
            </form>
            
            <div class="login-footer">
                <a href="/" class="btn btn-ghost btn-sm">&larr; Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>