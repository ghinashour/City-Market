<?php
require_once "includes/bootstrap.php";
include "includes/db.php";
include "includes/header.php";

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Add product to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // If product already in cart, update quantity
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }

    echo "<p style='color:green;'>Product added to cart!</p>";
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    unset($_SESSION['cart'][$remove_id]);
}

// Update quantities
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $id => $qty) {
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    echo "<p style='color:blue;'>Cart updated!</p>";
}
?>

<link rel="stylesheet" href="assets/css/cart.css">

<h2>Your Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>
    <form method="post" action="cart.php">
        <table border="1" cellpadding="10">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php
            $total = 0;
            foreach ($_SESSION['cart'] as $id => $qty) {
                $sql = "SELECT * FROM products WHERE id = $id";
                $result = mysqli_query($connection, $sql);
                $product = mysqli_fetch_assoc($result);
                $subtotal = $product['price'] * $qty;
                $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo $product['name']; ?></td>
                    <td>$<?php echo $product['price']; ?></td>
                    <td>
                        <input type="number" name="quantities[<?php echo $id; ?>]" value="<?php echo $qty; ?>" min="1"
                            max="<?php echo $product['stock']; ?>">
                    </td>
                    <td>$<?php echo $subtotal; ?></td>
                    <td><a href="cart.php?remove=<?php echo $id; ?>">Remove</a></td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="3" align="right"><strong>Total:</strong></td>
                <td colspan="2"><strong>$<?php echo $total; ?></strong></td>
            </tr>
        </table>
        <br>
        <button type="submit" name="update_cart">Update Cart</button>
        <a href="checkout.php"><button type="button">Proceed to Checkout</button></a>
    </form>

<?php else: ?>
    <p>Your cart is empty. <a href="products.php">Start shopping</a></p>
<?php endif; ?>

<?php
include "includes/footer.php";
?>