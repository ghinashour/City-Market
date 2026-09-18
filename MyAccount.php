<?php
require_once "includes/bootstrap.php";
// Prevent browser caching of account page so logged-out users won't see stale data
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
include "includes/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: register.php");
    exit();
}

// User is logged in
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'Customer';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';
$member_since = $_SESSION['member_since'] ?? 'Recently';

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if ($new_password !== $confirm_password) {
        $password_error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $password_error = "Password must be at least 6 characters long.";
    } else {
        // Get current password from database
        $query = "SELECT password_hash FROM users WHERE id = ?";
        $stmt = mysqli_prepare($connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($current_password, $user['password_hash'])) {
            // Update password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password_hash = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($connection, $update_query);
            mysqli_stmt_bind_param($update_stmt, "si", $new_password_hash, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $password_success = "Password changed successfully!";
            } else {
                $password_error = "Failed to update password. Please try again.";
            }
            mysqli_stmt_close($update_stmt);
        } else {
            $password_error = "Current password is incorrect.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $confirm_text = $_POST['confirm_delete'] ?? '';
    
    if ($confirm_text === "DELETE") {
        // Delete user from database
        $delete_query = "DELETE FROM users WHERE id = ?";
        $delete_stmt = mysqli_prepare($connection, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            // Logout and destroy session
            session_unset();
            session_destroy();
            setcookie(session_name(), '', time() - 3600, '/');
            
            // Redirect to home page
            header("Location: index.php");
            exit();
        }
        mysqli_stmt_close($delete_stmt);
    } else {
        $delete_error = "Please type DELETE to confirm account deletion.";
    }
}

// Fetch recent orders
$recent_orders = [];
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5";
$stmt = mysqli_prepare($connection, $query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_orders[] = $row;
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($connection);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | Local City Market</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "includes/header.php"; ?>

    <main class="account-container">
        <div class="account-sidebar">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2><?php echo htmlspecialchars($user_name); ?></h2>
                <p><?php echo htmlspecialchars($user_email); ?></p>
            </div>
            
            <nav class="account-nav">
                <a href="#profile" class="nav-item active">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="#orders" class="nav-item">
                    <i class="fas fa-shopping-bag"></i> My Orders
                </a>
                <a href="#settings" class="nav-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="logout.php" class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </div>
        
        <div class="account-content">
            <!-- Profile Section -->
            <section id="profile" class="account-section active">
                <h2><i class="fas fa-user"></i> My Profile</h2>
                <div class="profile-grid">
                    <div class="profile-card">
                        <h3>Personal Information</h3>
                        <div class="info-item">
                            <span class="label">Name:</span>
                            <span class="value"><?php echo htmlspecialchars($user_name); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Email:</span>
                            <span class="value"><?php echo htmlspecialchars($user_email); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Phone:</span>
                            <span class="value"><?php echo htmlspecialchars($user_phone ?: 'Not provided'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Member Since:</span>
                            <span class="value"><?php echo htmlspecialchars($member_since); ?></span>
                        </div>
                    </div>
                    
                    <div class="profile-card">
                        <h3>Account Summary</h3>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="stat-number"><?php echo count($recent_orders); ?></span>
                                <span class="stat-label">Orders</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-box"></i>
                                <span class="stat-number">
                                    <?php 
                                    $delivered = 0;
                                    foreach($recent_orders as $order) {
                                        if (isset($order['status']) && strtolower($order['status']) === 'delivered') {
                                            $delivered++;
                                        }
                                    }
                                    echo $delivered;
                                    ?>
                                </span>
                                <span class="stat-label">Delivered</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Orders Section -->
            <section id="orders" class="account-section">
                <h2><i class="fas fa-shopping-bag"></i> Order History</h2>
                <?php if(!empty($recent_orders)): ?>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent_orders as $order): 
                                $order_id = $order['order_id'] ?? $order['id'] ?? 'N/A';
                                $order_date = date('M d, Y', strtotime($order['order_date'] ?? $order['date'] ?? ''));
                                $total = $order['total_amount'] ?? $order['total'] ?? 0;
                                $status = $order['status'] ?? 'Pending';
                            ?>
                            <tr>
                                <td>#<?php echo htmlspecialchars($order_id); ?></td>
                                <td><?php echo $order_date; ?></td>
                                <td>$<?php echo number_format($total, 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($status); ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag fa-2x"></i>
                    <h3>No orders yet</h3>
                    <p>Your order history will appear here</p>
                    <a href="products.php" class="btn-primary">Shop Now</a>
                </div>
                <?php endif; ?>
            </section>
            
            <!-- Settings Section -->
            <section id="settings" class="account-section">
                <h2><i class="fas fa-cog"></i> Account Settings</h2>
                <div class="settings-grid">
                    <!-- Change Password Card -->
                    <div class="settings-card">
                        <h3>Change Password</h3>
                        
                        <?php if (isset($password_error)): ?>
                            <div class="alert alert-error"><?php echo $password_error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($password_success)): ?>
                            <div class="alert alert-success"><?php echo $password_success; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn-change-password">
                                Change Password
                            </button>
                        </form>
                    </div>
                    
                    <!-- Delete Account Card -->
                    <div class="settings-card">
                        <h3>Delete Account</h3>
                        
                        <?php if (isset($delete_error)): ?>
                            <div class="alert alert-error"><?php echo $delete_error; ?></div>
                        <?php endif; ?>
                        
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p><strong>Warning:</strong> This action cannot be undone. All your data will be permanently deleted.</p>
                        </div>
                        
                        <form method="post" action="" onsubmit="return confirmAccountDeletion()">
                            <div class="form-group">
                                <label for="confirm_delete">
                                    Type <strong>DELETE</strong> to confirm:
                                </label>
                                <input type="text" id="confirm_delete" name="confirm_delete" 
                                       placeholder="Type DELETE here" required>
                            </div>
                            
                            <button type="submit" name="delete_account" class="btn-delete-account">
                                <i class="fas fa-trash-alt"></i> Delete My Account
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include "includes/footer.php"; ?>
    
    <script>
        // Tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navItems = document.querySelectorAll('.nav-item');
            if (navItems.length > 0) {
                navItems.forEach(item => {
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Remove active class
                        navItems.forEach(nav => nav.classList.remove('active'));
                        
                        // Add active to clicked
                        this.classList.add('active');
                        
                        // Hide all sections
                        document.querySelectorAll('.account-section').forEach(section => {
                            section.classList.remove('active');
                        });
                        
                        // Show target
                        const targetId = this.getAttribute('href').substring(1);
                        const targetSection = document.getElementById(targetId);
                        if (targetSection) {
                            targetSection.classList.add('active');
                        }
                    });
                });
            }
        });
        
        // Confirm account deletion
        function confirmAccountDeletion() {
            const confirmText = document.getElementById('confirm_delete').value;
            if (confirmText !== "DELETE") {
                alert('Please type DELETE to confirm account deletion.');
                return false;
            }
            return confirm('Are you sure you want to delete your account? This action cannot be undone.');
        }
        
        // Logout confirmation
        document.querySelector('.logout').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>