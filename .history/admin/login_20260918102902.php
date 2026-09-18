<?php
require_once "../includes/bootstrap.php";
include "../includes/db.php";

$error = "";

if(isset($_POST['login'])){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($connection, "SELECT username, password FROM admins WHERE username = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (rate_limit('admin_login', 5, 900) && $admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin'] = $username;
        header("Location: products.php");
        exit;
    } else {
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
        <div class="error-message"><?php echo $error; ?></div>
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
