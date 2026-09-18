<?php require_once __DIR__ . '/bootstrap.php'; ?>
   <head>
        <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   </head>
    <!-- Header with Navigation -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <h1><i class="fas fa-leaf"></i>  City Market</h1>
                <p class="tagline">Fast Delivery to your home</p>
            </div>
            
            <nav class="main-nav">
                <a href="index.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                <a href="products.php" class="nav-link"><i class="fas fa-store"></i> Shop</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-count">0</span></a>
                <?php if (!empty(
                    
                    
                    
                    $_SESSION['user_id'])): ?>
                <a href="MyAccount.php" class="nav-link"><i class="fas fa-user"></i> Account</a>
                <?php else: ?>
                <a href="register.php" class="nav-link account"><i class="fas fa-user"></i> Account</a>
                <?php endif; ?>
            </nav>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    
    </header>

    <script>
         // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.main-nav')?.classList.toggle('show');
        });
    </script>
