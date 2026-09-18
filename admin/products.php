<?php
session_start();
if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit;
}

include "../includes/db.php";

// ADD PRODUCT
if(isset($_POST['add_product'])){
    $name = mysqli_real_escape_string($connection, $_POST['name']);
    $price = mysqli_real_escape_string($connection, $_POST['price']);
    $stock = mysqli_real_escape_string($connection, $_POST['stock']);
    $category_id = mysqli_real_escape_string($connection, $_POST['category_id']);
    $description = mysqli_real_escape_string($connection, $_POST['description']);

    $sql = "INSERT INTO products (name, price, stock, category_id, description)
            VALUES ('$name', '$price', '$stock', '$category_id', '$description')";
    mysqli_query($connection, $sql);
    header("Location: products.php");
    exit;
}

// DELETE PRODUCT
if(isset($_GET['delete'])){
    $id = mysqli_real_escape_string($connection, $_GET['delete']);
    mysqli_query($connection, "DELETE FROM products WHERE id = $id");
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Products</title>
    <link rel="stylesheet" href="../admin/css/products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header2.php'; ?>
    
    <div class="container">
        <div class="content">
            <!-- Add Product Form -->
            <div class="card">
                <h2><i class="fas fa-plus-circle"></i> Add New Product</h2>
                <form method="post" class="form">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" required placeholder="Enter product name">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Price:</label>
                            <input type="number" step="0.01" name="price" required placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label>Stock:</label>
                            <input type="number" name="stock" required placeholder="Enter quantity">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Category:</label>
                        <select name="category_id" required>
                            <option value="">Select category</option>
                            <?php
                            $cats = mysqli_query($connection, "SELECT * FROM categories");
                            while($cat = mysqli_fetch_assoc($cats)){
                                echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea name="description" rows="3" placeholder="Enter product description"></textarea>
                    </div>
                    
                    <button type="submit" name="add_product" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Product
                    </button>
                </form>
            </div>
            
            <!-- Products Table -->
            <div class="card">
                <h2><i class="fas fa-boxes"></i> Product List</h2>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Category</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, c.name AS category 
                                    FROM products p 
                                    LEFT JOIN categories c ON p.category_id = c.id 
                                    ORDER BY p.id DESC";
                            $result = mysqli_query($connection, $sql);
                            
                            if(mysqli_num_rows($result) > 0) {
                                while($row = mysqli_fetch_assoc($result)){
                                    $stock_class = $row['stock'] < 10 ? 'stock-low' : ($row['stock'] < 50 ? 'stock-medium' : 'stock-good');
                                    echo "<tr>";
                                    echo "<td>#{$row['id']}</td>";
                                    echo "<td>{$row['name']}</td>";
                                    echo "<td>\${$row['price']}</td>";
                                    echo "<td><span class='stock $stock_class'>{$row['stock']}</span></td>";
                                    echo "<td>{$row['category']}</td>";
                                    echo "<td>
                                            <a href='products.php?delete={$row['id']}' 
                                               class='btn-delete' 
                                               onclick='return confirmDelete()'>
                                               <i class='fas fa-trash'></i> Delete
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center'>No products found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete() {
        return confirm('Are you sure you want to delete this product?');
    }
    
    // Mobile menu toggle
    document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
        document.querySelector('.main-nav')?.classList.toggle('show');
    });
    </script>
</body>
</html>