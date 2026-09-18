<?php
// Include database connection
include "includes/db.php";

session_start();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}


// Add product to cart if submitted from home page
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // If product already in cart, update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
    
    //  Show success message
    $cart_success = "Product added to cart!";

}


// Check if database connection is successful
$has_db_connection = isset($connection) && $connection !== false;

// Default fallback specials (always available even if no data found)
$fallback_specials = [
    ['id' => 1, 'name' => 'Organic Apples', 'price' => 3.99, 'sale_price' => 2.99, 'image' => 'grocery1.jfif', 'desc' => 'Crisp, fresh-picked organic apples'],
    ['id' => 2, 'name' => 'Fresh Bread', 'price' => 4.50, 'sale_price' => 3.50, 'image' => 'grocery2.jfif', 'desc' => 'Artisan sourdough loaf'],
    ['id' => 3, 'name' => 'Farm Eggs', 'price' => 5.99, 'sale_price' => 4.49, 'image' => 'grocery3.jfif', 'desc' => 'Free-range farm eggs'],
    ['id' => 4, 'name' => 'Avocados', 'price' => 2.50, 'sale_price' => 1.75, 'image' => 'store.webp', 'desc' => 'Fresh Hass avocados']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Local City Market | Fresh Groceries</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Header with Navigation -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo-area">
                <h1><i class="fas fa-leaf"></i>  City Market</h1>
                <p class="tagline">Fast Delivery to your home</p>
            </div>
            
            <nav class="main-nav">
                <a href="#" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                <a href="products.php" class="nav-link"><i class="fas fa-store"></i> Shop</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-count">0</span></a>
                <a href="MyAccount.php" class="nav-link"><i class="fas fa-user"></i> Account</a>
            </nav>
            
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
        
    
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Fresh Groceries, <span class="highlight">Local Love</span></h1>
                <p class="hero-subtitle">We deliver farm-fresh produce and pantry essentials straight from your neighborhood market</p>
                <div class="hero-actions">
                    <a href="products.php" class="btn btn-primary"><i class="fas fa-shopping-basket"></i> Start Shopping</a>
                    <a href="#weekly-specials" class="btn btn-secondary">View Weekly Specials</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="floating-badge">
                    <span>100%</span>
                    <small>Local Sources</small>
                </div>
            </div>
        </div>
        
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat">
                <i class="fas fa-truck"></i>
                <h3>Free Delivery</h3>
                <p>On orders over $50</p>
            </div>
            <div class="stat">
                <i class="fas fa-leaf"></i>
                <h3>Fresh Daily</h3>
                <p>Produce delivered daily</p>
            </div>
            <div class="stat">
                <i class="fas fa-clock"></i>
                <h3>Open 7 Days</h3>
                <p>7AM - 10PM</p>
            </div>
            <div class="stat">
                <i class="fas fa-handshake"></i>
                <h3>Local Partners</h3>
                <p>25+ local farms</p>
            </div>
        </div>
    </section>


       <!-- Show success message if product was added -->
    <?php if (isset($cart_success)): ?>
        <div class="cart-success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 4px;">
            <?php echo $cart_success; ?> <a href="cart.php" style="color: #155724; font-weight: bold;">View Cart</a>
        </div>
    <?php endif; ?>

    

    <!-- Weekly Specials -->
    <section class="weekly-specials" id="weekly-specials">
        <div class="section-header">
            <h2><i class="fas fa-star"></i> This Week's Specials</h2>
            <a href="products.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="specials-grid">
            <?php
            $show_fallback = true; // Default to showing fallback
            
            // Check if database connection exists and try to get products
            if ($has_db_connection) {
                try {
                    // First, check if the products table exists
                    $table_check = mysqli_query($connection, "SHOW TABLES LIKE 'products'");
                    
                    if ($table_check && mysqli_num_rows($table_check) > 0) {
                        // Table exists, try to get featured products
                        // Use a simpler query that won't fail if columns don't exist
                        $special_query = "SELECT * FROM products LIMIT 4";
                        $special_result = mysqli_query($connection, $special_query);
                        
                        if($special_result && mysqli_num_rows($special_result) > 0) {
                            $show_fallback = false; // Don't show fallback since we have real products
                            while($row = mysqli_fetch_assoc($special_result)) {
                                // Check if columns exist, use defaults if not
                                $name = isset($row['name']) ? htmlspecialchars($row['name']) : 'Product';
                                $desc = isset($row['description']) ? htmlspecialchars($row['description']) : 'Fresh product from our market';
                                $price = isset($row['price']) ? floatval($row['price']) : 5.99;
                                $sale_price = $price * 0.8; // 20% off for specials
                                $image = isset($row['image']) ? 'assets/images/products/' . htmlspecialchars($row['image']) : 'assets/images/store.webp';
                                $id = isset($row['id']) ? $row['id'] : 0;
                                
                                echo '
                                <div class="product-card">
                                    <div class="product-badge">Sale</div>
                                    <div class="product-image">
                                        <img src="'.$image.'" alt="'.$name.'" onerror="this.src=\'assets/images/store.webp\'">
                                    </div>
                                    <div class="product-info">
                                        <h3>'.$name.'</h3>
                                        <p class="product-desc">'.$desc.'</p>
                                        <div class="product-pricing">
                                            <span class="current-price">$'.number_format($sale_price, 2).'</span>
                                            <span class="original-price">$'.number_format($price, 2).'</span>
                                        </div>
                                        <form method="post" action="" class="add-to-cart-form">
                                            <input type="hidden" name="product_id" value="'.$id.'">
                                            <input type="hidden" name="quantity" value="1">
                                            <button type="submit" name="add_to_cart" class="btn-add-to-cart">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>';
                            }
                        }
                    }
                } catch (Exception $e) {
                    // If any error occurs, show fallback
                    $show_fallback = true;
                }
            }
            
            // Show fallback specials if needed
            if ($show_fallback) {
                foreach($fallback_specials as $item) {
                    echo '
                    <div class="product-card">
                        <div class="product-badge">Sale</div>
                        <div class="product-image">
                            <img src="assets/images/'.$item['image'].'" alt="'.$item['name'].'">
                        </div>
                        <div class="product-info">
                            <h3>'.$item['name'].'</h3>
                            <p class="product-desc">'.$item['desc'].'</p>
                            <div class="product-pricing">
                                <span class="current-price">$'.number_format($item['sale_price'], 2).'</span>
                                <span class="original-price">$'.number_format($item['price'], 2).'</span>
                            </div>
                            <button class="btn-add-to-cart" data-id="'.$item['id'].'"><i class="fas fa-cart-plus"></i> Add to Cart</button>
                        </div>
                    </div>';
                }
            }
            ?>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <div class="about-content">
            <div class="about-text">
                <h2>Your Neighborhood Market, Reimagined</h2>
                <p>Local City Market brings the freshness of farmer's markets with the convenience of modern shopping. We partner directly with local producers to ensure you get the highest quality groceries while supporting our community.</p>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-seedling"></i>
                        <h4>Sustainably Sourced</h4>
                        <p>All products come from ethical, sustainable sources</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bolt"></i>
                        <h4>Fast Delivery</h4>
                        <p>Get your groceries in under 2 hours</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-heart"></i>
                        <h4>Community Focused</h4>
                        <p>We reinvest 5% of profits into local initiatives</p>
                    </div>
                </div>
                
                <a href="#" class="btn btn-outline">Learn More About Us</a>
            </div>
            <div class="about-image">
                <img src="assets/images/store.webp" alt="Our Modern Store">
            </div>
        </div>
    </section>

    <!-- Category Browse -->
    <section class="categories">
        <h2>Shop by Category</h2>
        <div class="category-grid">
            <a href="products.php?category=fruits" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-apple-alt"></i>
                </div>
                <h3>Fresh Fruits</h3>
                <p>Seasonal & organic</p>
            </a>
            <a href="products.php?category=vegetables" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-carrot"></i>
                </div>
                <h3>Vegetables</h3>
                <p>Farm to table</p>
            </a>
            <a href="products.php?category=dairy" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-cheese"></i>
                </div>
                <h3>Dairy & Eggs</h3>
                <p>Local farms</p>
            </a>
            <a href="products.php?category=meat" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-drumstick-bite"></i>
                </div>
                <h3>Meat & Poultry</h3>
                <p>Premium quality</p>
            </a>
            <a href="products.php?category=bakery" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-bread-slice"></i>
                </div>
                <h3>Bakery</h3>
                <p>Freshly baked</p>
            </a>
            <a href="products.php?category=snacks" class="category-card">
                <div class="category-icon">
                    <i class="fas fa-cookie-bite"></i>
                </div>
                <h3>Pantry & Snacks</h3>
                <p>Your favorites</p>
            </a>
        </div>
    </section>

    <!-- Newsletter -->
    <section class="newsletter">
        <div class="newsletter-content">
            <div class="newsletter-text">
                <h2>Get Weekly Deals</h2>
                <p>Subscribe to our newsletter and get 10% off your first order plus weekly specials delivered to your inbox.</p>
            </div>
            <form class="newsletter-form">
                <div class="form-group">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </div>
                <p class="form-note">By subscribing you agree to our Privacy Policy</p>
            </form>
        </div>
    </section>

    <?php
    include "includes/footer.php";
    ?>
    
    <!-- Simple JS for interactions -->
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.main-nav')?.classList.toggle('show');
        });
          
        // Newsletter form submission
        document.querySelector('.newsletter-form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            alert(`Thank you! You've subscribed with: ${email}`);
            this.reset();
        });
    </script>
</body>
</html>