<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header with Navigation -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <h1><i class="fas fa-leaf"></i> Admin Panel</h1>
                <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?></p>
            </div>
            
            <nav class="main-nav" role="navigation" aria-label="Admin navigation">
                <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-store"></i> Products
                </a>
                <a href="orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="logout.php" class="nav-link logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>