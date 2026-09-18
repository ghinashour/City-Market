<?php
require_once "../includes/bootstrap.php";
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

include "../includes/db.php";

/** @var mysqli $connection */

// Update order status
if(isset($_POST['update_status'])){
    $order_id = mysqli_real_escape_string($connection, $_POST['order_id']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);

    mysqli_query($connection, "UPDATE orders SET status='$status' WHERE id=$order_id");
    header("Location: orders.php");
    exit;
}

// Toggle payment status
if(isset($_POST['toggle_payment'])){
    $order_id = mysqli_real_escape_string($connection, $_POST['order_id']);
    
    // Toggle payment status (assuming there's a 'paid' column in orders table)
    // If you don't have this column, you'll need to add it: ALTER TABLE orders ADD paid BOOLEAN DEFAULT 0;
    mysqli_query($connection, "UPDATE orders SET paid = NOT paid WHERE id=$order_id");
    header("Location: orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Orders</title>
    <link rel="stylesheet" href="../admin/css/orders.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header2.php'; ?>
    
    <div class="container">
        <div class="content">
            <!-- Orders Table -->
            <div class="card">
                <h2><i class="fas fa-shopping-cart"></i> Orders</h2>
                
                <div class="table-responsive">
                    <table class="simple-table">
                        <thead>
                            <tr>
                                <th class="check-col">Paid</th>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $orders = mysqli_query($connection, "SELECT * FROM orders ORDER BY order_date DESC");
                            
                            if(mysqli_num_rows($orders) > 0) {
                                while($order = mysqli_fetch_assoc($orders)){
                                    $status_class = strtolower($order['status']);
                                    $paid = isset($order['paid']) ? $order['paid'] : 0;
                                    
                                    echo "<tr>";
                                    echo "<td class='check-col'>
                                            <form method='post' class='payment-form'>
                                                <input type='hidden' name='order_id' value='{$order['id']}'>
                                                <button type='submit' name='toggle_payment' class='payment-checkbox " . ($paid ? 'paid' : 'unpaid') . "'>
                                                    <i class='fas " . ($paid ? 'fa-check-circle' : 'fa-circle') . "'></i>
                                                </button>
                                            </form>
                                          </td>";
                                    echo "<td>#{$order['id']}</td>";
                                    echo "<td>
                                            <div class='simple-customer'>
                                                <strong>{$order['customer_name']}</strong>
                                                <small>{$order['phone']}</small>
                                            </div>
                                          </td>";
                                    echo "<td>\${$order['total_price']}</td>";
                                    echo "<td>
                                            <form method='post' class='status-form'>
                                                <input type='hidden' name='order_id' value='{$order['id']}'>
                                                <select name='status' class='status-select status-{$status_class}' onchange='this.form.submit()'>
                                                    <option value='Pending' ".($order['status']=="Pending"?"selected":"").">Pending</option>
                                                    <option value='Processing' ".($order['status']=="Processing"?"selected":"").">Processing</option>
                                                    <option value='Delivered' ".($order['status']=="Delivered"?"selected":"").">Delivered</option>
                                                </select>
                                            </form>
                                          </td>";
                                    echo "<td>" . date('M d', strtotime($order['order_date'])) . "</td>";
                                    echo "<td class='actions'>
                                            <a href='orders.php?view={$order['id']}' class='btn-action'>
                                                <i class='fas fa-eye'></i>
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>No orders found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Order Details - Simplified -->
            <?php
            if(isset($_GET['view'])){
                $order_id = mysqli_real_escape_string($connection, $_GET['view']);
                
                // Get order info
                $order_query = mysqli_query($connection, "SELECT * FROM orders WHERE id = $order_id");
                $order_info = mysqli_fetch_assoc($order_query);
                $paid = isset($order_info['paid']) ? $order_info['paid'] : 0;
                
                echo '<div class="card">';
                echo '<div class="order-header">';
                echo '<h3><i class="fas fa-receipt"></i> Order #' . $order_id . '</h3>';
                echo '<form method="post" class="payment-form-detail">
                        <input type="hidden" name="order_id" value="' . $order_id . '">
                        <button type="submit" name="toggle_payment" class="payment-toggle ' . ($paid ? 'paid' : 'unpaid') . '">
                            ' . ($paid ? 'Paid <i class="fas fa-check-circle"></i>' : 'Mark as Paid <i class="fas fa-circle"></i>') . '
                        </button>
                      </form>';
                echo '</div>';
                
                echo '<div class="simple-summary">';
                echo '<div class="summary-item">
                        <label>Customer:</label>
                        <span>' . $order_info['customer_name'] . '</span>
                      </div>';
                echo '<div class="summary-item">
                        <label>Phone:</label>
                        <span>' . $order_info['phone'] . '</span>
                      </div>';
                echo '<div class="summary-item">
                        <label>Address:</label>
                        <span>' . $order_info['address'] . '</span>
                      </div>';
                echo '<div class="summary-item">
                        <label>Total:</label>
                        <span class="total-amount">$' . $order_info['total_price'] . '</span>
                      </div>';
                echo '<div class="summary-item">
                        <label>Status:</label>
                        <span class="status-badge status-' . strtolower($order_info['status']) . '">' . $order_info['status'] . '</span>
                      </div>';
                echo '<div class="summary-item">
                        <label>Date:</label>
                        <span>' . date('F d, Y', strtotime($order_info['order_date'])) . '</span>
                      </div>';
                echo '</div>';
                
                // Order items
                $items = mysqli_query($connection, "
                    SELECT oi.*, p.name 
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = $order_id
                ");
                
                if(mysqli_num_rows($items) > 0) {
                    echo '<div class="order-items">';
                    echo '<table class="simple-table">';
                    echo '<thead>';
                    echo '<tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>';
                    echo '</thead>';
                    echo '<tbody>';
                    
                    while($item = mysqli_fetch_assoc($items)){
                        $item_total = $item['quantity'] * $item['price'];
                        echo '<tr>';
                        echo '<td>' . $item['name'] . '</td>';
                        echo '<td>' . $item['quantity'] . '</td>';
                        echo '<td>$' . $item['price'] . '</td>';
                        echo '<td>$' . number_format($item_total, 2) . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</tbody>';
                    echo '</table>';
                    echo '</div>';
                }
                
                echo '<div class="order-footer">';
                echo '<a href="orders.php" class="btn-back">Back to Orders</a>';
                echo '</div>';
                
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <script>
    // Mobile menu toggle
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.main-nav')?.classList.toggle('show');
    });
    
    // Payment toggle animation
    document.querySelectorAll('.payment-checkbox, .payment-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            const originalText = this.innerHTML;
            
            // Add click animation
            this.style.transform = 'scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });
    
    // Auto-submit status forms
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            form.submit();
        });
    });
    </script>
</body>
</html>