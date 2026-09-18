<?php
require_once "../includes/bootstrap.php";
include "../includes/db.php";

/** @var mysqli $connection */
$error = "";

if(isset($_POST['login'])){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin_username = '';
    $admin_password_hash = '';
    $stmt = mysqli_prepare($connection, "SELECT username, password FROM admins WHERE username = ? LIMIT 1");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $admin_username, $admin_password_hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log('Admin login query failed: ' . mysqli_error($connection));
        $error = 'Admin login is temporarily unavailable. Please try again later.';
    }

    if (!$error && rate_limit('admin_login', 5, 900) && $admin_password_hash && password_verify($password, $admin_password_hash)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $admin_username;
        header("Location: products.php");
        exit;
    } elseif (!$error) {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <header class="login-header">
        <h1 class="login-title">Admin Login</h1>
    </header>
    
    <div class="login-container">
        <?php if($error): ?>
        <div class="error-message"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter your username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter your password">
            </div>
            
            <button type="submit" name="login" class="submit-btn">Login</button>
            
            <p class="login-hint">
                Contact administrator if you forgot credentials
            </p>
        </form>
    </div>
</body>
</html>
