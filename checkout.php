<?php
require_once "includes/bootstrap.php";
include "includes/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p>Please <a href='login.php'>login</a> to checkout.</p>";
    include "includes/footer.php";
    exit;
}

// Redirect to products page if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='products.php'>Start shopping</a></p>";
    include "includes/footer.php";
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_phone = $_SESSION['user_phone'] ?? '';

// Process form submission
if (isset($_POST['place_order'])) {
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $phone = mysqli_real_escape_string($connection, $_POST['phone']);
    $address = mysqli_real_escape_string($connection, $_POST['address']);
    $total_price = 0;

    // Calculate total
    foreach ($_SESSION['cart'] as $id => $qty) {
        $sql = "SELECT price FROM products WHERE id = $id";
        $result = mysqli_query($connection, $sql);
        $product = mysqli_fetch_assoc($result);
        $total_price += $product['price'] * $qty;
    }

    // Insert into orders table WITH user_id
    $sql_order = "INSERT INTO orders (user_id, customer_name, phone, address, total_price, status, order_date) 
                  VALUES ('$user_id', '$name', '$phone', '$address', '$total_price', 'Pending', NOW())";
    
    if (mysqli_query($connection, $sql_order)) {
        $order_id = mysqli_insert_id($connection);

        // Insert into order_items table with order_id
        foreach ($_SESSION['cart'] as $id => $qty) {
            $sql_item = "SELECT price, name FROM products WHERE id = $id";
            $result = mysqli_query($connection, $sql_item);
            $product = mysqli_fetch_assoc($result);
            $price = $product['price'];
            $product_name = $product['name'];
            
            // Insert into order_items
            mysqli_query($connection, "INSERT INTO order_items (order_id, product_id, product_name, quantity, price) 
                                 VALUES ($order_id, $id, '$product_name', $qty, $price)");
        }

        // Clear cart
        $_SESSION['cart'] = array();

        // Redirect to success page
        header("Location: order_success.php?id=$order_id");
        exit;
    } else {
        echo "Error: " . mysqli_error($connection);
    }
}
?>

<link rel="stylesheet" href="assets/css/checkout.css">
<?php include "includes/header.php"; ?>
<h2>Checkout</h2>
<div class="order-summary">
    <h3>Order Summary</h3>
    <?php
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $sql = "SELECT name, price FROM products WHERE id = $id";
        $result = mysqli_query($connection, $sql);
        $product = mysqli_fetch_assoc($result);
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
        echo '<div class="order-item">
                <span class="item-name">'.$product['name'].'</span>
                <div>
                    <span class="item-quantity">'.$qty.' × $'.$product['price'].'</span>
                    <span class="item-price">$'.$subtotal.'</span>
                </div>
              </div>';
    }
    ?>
    <div class="order-total">
        <span>Total:</span>
        <span class="total-amount">$<?php echo number_format($total, 2); ?></span>
    </div>
</div>
<form method="post" action="checkout.php">
    <label>Full Name:</label><br>
    <input type="text" name="name" value="<?php echo htmlspecialchars($user_name); ?>" required><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($user_phone); ?>" required><br><br>

    <label>Delivery Address:</label><br>
    <textarea name="address" rows="4" required></textarea><br><br>

    <button type="submit" name="place_order">Place Order</button>
</form>

<?php include "includes/footer.php"; ?>