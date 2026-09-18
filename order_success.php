<?php
include "includes/db.php";
include "includes/header.php";

// Check if order ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];

    // Fetch order details
    $sql_order = "SELECT * FROM orders WHERE id = $order_id";
    $result_order = mysqli_query($connection, $sql_order);

    if (mysqli_num_rows($result_order) > 0) {
        $order = mysqli_fetch_assoc($result_order);
        echo "<h2>Thank you, " . $order['customer_name'] . "!</h2>";
        echo "<p>Your order has been placed successfully.</p>";
        echo "<p><strong>Order ID:</strong> " . $order['id'] . "</p>";
        echo "<p><strong>Total Price:</strong> $" . $order['total_price'] . "</p>";
        echo "<p><strong>Status:</strong> " . $order['status'] . "</p>";
        echo "<p><strong>Delivery Address:</strong> " . $order['address'] . "</p>";

        // Fetch order items
        $sql_items = "SELECT oi.*, p.name FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.id
                      WHERE oi.order_id = $order_id";
        $result_items = mysqli_query($connection, $sql_items);

        echo "<h3>Order Details:</h3>";
        echo "<ul>";
        while ($item = mysqli_fetch_assoc($result_items)) {
            echo "<li>" . $item['name'] . " - Quantity: " . $item['quantity'] . " - Price: $" . $item['price'] . "</li>";
        }
        echo "</ul>";

        echo "<p><a href='index.php'>Back to Home</a></p>";

    } else {
        echo "<p>Order not found.</p>";
    }

} else {
    echo "<p>Invalid order ID.</p>";
}

include "includes/footer.php";
?>