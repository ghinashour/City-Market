<link rel="stylesheet" href="assets/css/details.css">

<?php
session_start();
include "includes/db.php";
include "includes/header.php";

// Check if product ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Fetch product from DB using prepared statement
    $sql = "SELECT p.*, c.name AS category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ?";
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $product = mysqli_fetch_assoc($result);
        
        // Use the same method as products page for image display
        // Check if column is 'image' or 'image_url' - using same logic as products page
        $image_column = isset($product['image']) ? 'image' : 'image_url';
        $image_filename = $product[$image_column] ?? '';
        
        // Build image path same as products page
        $image_path = 'assets/images/products/' . $image_filename;
        $default_image = 'assets/images/store.webp'; // Using same default as products page
        $actual_image = (!empty($image_filename) && file_exists($image_path)) ? $image_path : $default_image;
        ?>
        <main class="product-details-container">
            <div class="product-details">
                <!-- Product Image & Basic Info -->
                <div class="product-main">

                    <div class="product-image">
                        <img src="<?php echo $actual_image; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='<?php echo $default_image; ?>'">
                    </div>
                    
                    <div class="product-info">
                        <div class="product-header">
                            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></span>
                        </div>
                        
                        <div class="product-price">
                            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                            <?php if ($product['stock'] <= 10 && $product['stock'] > 0): ?>
                                <span class="stock-low">Only <?php echo $product['stock']; ?> left in stock!</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-stock">
                            <span class="stock-status <?php echo ($product['stock'] > 0) ? 'in-stock' : 'out-of-stock'; ?>">
                                <i class="fas <?php echo ($product['stock'] > 0) ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                <?php echo ($product['stock'] > 0) ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        
                        <form action="cart.php" method="post" class="add-to-cart-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="quantity-selector">
                                <label for="quantity">Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn minus" onclick="decreaseQty()">-</button>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" 
                                           max="<?php echo $product['stock']; ?>" readonly>
                                    <button type="button" class="qty-btn plus" onclick="increaseQty()">+</button>
                                </div>
                                <span class="max-qty">Max: <?php echo $product['stock']; ?></span>
                            </div>
                            
                            <button type="submit" name="add_to_cart" class="add-to-cart-btn" 
                                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i>
                                <?php echo ($product['stock'] > 0) ? 'Add to Cart' : 'Out of Stock'; ?>
                            </button>
                        </form>
                    
                    </div>
                </div>
                
              
            </div>
        </main>
        
        <script>
        // Quantity controls
        function increaseQty() {
            const input = document.getElementById('quantity');
            const max = parseInt(input.max);
            const current = parseInt(input.value);
            if (current < max) {
                input.value = current + 1;
            }
        }
        
        function decreaseQty() {
            const input = document.getElementById('quantity');
            const min = parseInt(input.min);
            const current = parseInt(input.value);
            if (current > min) {
                input.value = current - 1;
            }
        }
        
        // Tab functionality
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and panes
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
                
                // Add active class to clicked button
                button.classList.add('active');
                
                // Show corresponding tab content
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Add to cart form submission
        document.querySelector('.add-to-cart-form').addEventListener('submit', function(e) {
            if (<?php echo $product['stock']; ?> <= 0) {
                e.preventDefault();
                alert('This product is out of stock.');
            }
        });
        </script>
        <?php
    } else {
        echo '<div class="product-not-found">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>Product Not Found</h2>
                <p>The product you are looking for does not exist or has been removed.</p>
                <a href="products.php" class="btn-primary">Continue Shopping</a>
              </div>';
    }
    mysqli_stmt_close($stmt);
} else {
    echo '<div class="product-not-found">
            <i class="fas fa-exclamation-circle"></i>
            <h2>Invalid Product</h2>
            <p>Please select a valid product.</p>
            <a href="products.php" class="btn-primary">Browse Products</a>
          </div>';
}

mysqli_close($connection);
include "includes/footer.php";
?>