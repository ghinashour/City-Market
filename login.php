<?php
require_once "includes/bootstrap.php";
include "includes/db.php";

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: MyAccount.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!rate_limit('login', 5, 900)) {
        $error = 'Too many login attempts. Please try again in 15 minutes.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Use prepared statement
        $query = "SELECT id, full_name, email, phone, password_hash, created_at FROM users WHERE email = ?";
        $stmt = mysqli_prepare($connection, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($user) {
                if (password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_phone'] = $user['phone'] ?? '';
                    $_SESSION['member_since'] = date('F Y', strtotime($user['created_at']));
                    
                    $redirect_url = $_SESSION['redirect_url'] ?? 'MyAccount.php';
                    unset($_SESSION['redirect_url']);
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    $error = 'Invalid password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Database error. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Local City Market</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <main class="auth-container">
        <div class="auth-card">
            <h1>Login to Your Account</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? 'test@example.com'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small><a href="forgot_password.php">Forgot password?</a></small>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Create one now</a></p>
                <p>Continue as guest? <a href="products.php">Browse products</a></p>
            </div>
            
        </div>
    </main>
    
    <?php include "includes/footer.php"; ?>
</body>
</html>