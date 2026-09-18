<?php
require_once "../includes/bootstrap.php";
include "../includes/db.php";

/** @var mysqli $connection */
$error = "";

if(isset($_POST['login'])){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin_username = '';
    $admin_id = 0;
    $admin_password_hash = '';
    $stmt = mysqli_prepare($connection, "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $admin_id, $admin_username, $admin_password_hash);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log('Admin login query failed: ' . mysqli_error($connection));
        $error = 'Admin login is temporarily unavailable. Please try again later.';
    }

    $password_is_valid = $admin_password_hash !== '' && (
        password_verify($password, $admin_password_hash) ||
        hash_equals($admin_password_hash, $password)
    );

    if (!$error && rate_limit('admin_login', 5, 900) && $password_is_valid) {
        if (password_get_info($admin_password_hash)['algo'] === 0) {
            $new_hash = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = mysqli_prepare($connection, "UPDATE admins SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($update_stmt, "si", $new_hash, $admin_id);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);
        }
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
