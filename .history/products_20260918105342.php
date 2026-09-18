<?php
require_once "includes/bootstrap.php";

include "includes/db.php";
include "includes/header.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add product to cart if form submitted from this page
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validate quantity
    if ($quantity > 0) {
        // If product already in cart, update quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
    }
    
    // Optional: Show success message
    $cart_success = "Product added to cart!";
}

// Get search and filter parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($connection, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($connection, $_GET['category']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 12;

// used to  select all the products that we have
$sql = "SELECT p.id, p.name, p.description, p.price, p.stock, p.image, 
               c.name AS category 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if (!empty($category)) {
    $sql .= " AND c.name = '$category'";
}

// Add sorting
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
    default:
        $sql .= " ORDER BY p.name ASC";
        break;
}

$count_sql = "SELECT COUNT(*) AS total FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
if (!empty($search)) {
    $count_sql .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}
if (!empty($category)) {
    $count_sql .= " AND c.name = '$category'";
}
$count_result = mysqli_query($connection, $count_sql);
$total_products = (int) mysqli_fetch_assoc($count_result)['total'];
$total_pages = max(1, (int) ceil($total_products / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;
$sql .= " LIMIT $per_page OFFSET $offset";
$result = mysqli_query($connection, $sql);

$pagination_query = http_build_query([
    'search' => $search,
    'category' => $category,
    'sort' => $sort
]);
?>

<head>
    <link rel="stylesheet" href="assets/css/products.css">
</head>

<div class="products-container">
    <div class="products-header">
        <h1>Our Products</h1>
        <p>Fresh items from local producers</p>
    </div>

    <!-- Show success message if product was added -->
    <?php if (isset($cart_success)): ?>
        <div class="cart-success-message" style="background-color: #d4edda; color: #155724; padding: 10px; margin: 10px 0; border-radius: 4px;">
            <?php echo $cart_success; ?> <a href="cart.php" style="color: #155724; font-weight: bold;">View Cart</a>
        </div>
    <?php endif; ?>

    <!-- Filter and Search -->
    <div class="products-filters">
        <form method="GET" class="filter-form">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            
            <div class="filter-controls">
                <select name="category">
                    <option value="">All Categories</option>
                    <?php
                    $cat_query = "SELECT DISTINCT name FROM categories ORDER BY name";
                    $cat_result = mysqli_query($connection, $cat_query);
                    while($cat = mysqli_fetch_assoc($cat_result)) {
                        $selected = ($category == $cat['name']) ? 'selected' : '';
                        echo '<option value="'.$cat['name'].'" '.$selected.'>'.$cat['name'].'</option>';
                    }
                    ?>
                </select>
                
                <select name="sort">
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                </select>
                
                <button type="submit" class="btn-filter">Apply</button>
                <?php if(!empty($search) || !empty($category)): ?>
                    <a href="products.php" class="btn-clear">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="products-grid">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $image_path = 'assets/images/products/' . $row['image'];
                $default_image = 'assets/images/store.webp';
                $actual_image = file_exists($image_path) ? $image_path : $default_image;
            ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo $actual_image; ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         onerror="this.src='<?php echo $default_image; ?>'">
                </div>
                
                <div class="product-info">
                    <div class="product-header">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <span class="product-category"><?php echo $row['category']; ?></span>
                    </div>
                    
                    <p class="product-description">
                        <?php 
                        $desc = $row['description'];
                        echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                        ?>
                    </p>
                    
                    <div class="product-footer">
                        <div class="product-price">
                            <span class="price">$<?php echo number_format($row['price'], 2); ?></span>
                            <span class="stock <?php echo $row['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                                <?php echo $row['stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                        
                        <div class="product-actions">
                            <a href="product_details.php?id=<?php echo $row['id']; ?>" 
                               class="btn-view">View</a>
                            
                            <!-- Add to Cart Form -->
                            <form method="post" action="" class="add-to-cart-form">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="btn-cart" 
                                        <?php echo $row['stock'] <= 0 ? 'disabled' : ''; ?>>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-products">
                <i class="fas fa-search"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search or filters</p>
                <a href="products.php" class="btn-clear">Clear all filters</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav class="pagination" aria-label="Product pages">
            <?php for ($page_number = 1; $page_number <= $total_pages; $page_number++): ?>
                <a href="products.php?<?php echo $pagination_query; ?>&page=<?php echo $page_number; ?>"
                   class="<?php echo $page_number === $page ? 'active' : ''; ?>">
                    <?php echo $page_number; ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</div>

<?php include "includes/footer.php"; ?>