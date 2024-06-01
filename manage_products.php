<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";
//to show user infos sidebar
$userId = $_SESSION["user_id"];
$roleId = $_SESSION["role_id"];


// Fetch user information
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Handle form submission for adding a new product
if (isset($_POST["add_product"])) {
    $productName = $_POST["product_name"];
    $quantity = $_POST["quantity"];
    $warehouseId = $_POST["warehouse_id"];
    $stmt = $conn->prepare("INSERT INTO products (user_id, warehouse_id, product_name, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $userId, $warehouseId, $productName, $quantity);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_products.php");
    exit();
}

// Handle form submission for editing a product
if (isset($_POST["edit_product"])) {
    $productId = $_POST["product_id"];
    $productName = $_POST["product_name"];
    $quantity = $_POST["quantity"];
    $warehouseId = $_POST["warehouse_id"];
    $stmt = $conn->prepare("UPDATE products SET warehouse_id = ?, product_name = ?, quantity = ? WHERE id = ?");
    $stmt->bind_param("isii", $warehouseId, $productName, $quantity, $productId);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_products.php");
    exit();
}

// Handle deletion of a product
if (isset($_GET["delete_id"])) {
    $productId = $_GET["delete_id"];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_products.php");
    exit();
}

// Fetch all products
if ($roleId == 1) { // Admin role
    $stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name, users.full_name as user_full_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id JOIN users ON products.user_id = users.id");
} else {
    $stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id WHERE products.user_id = ?");
    $stmt->bind_param("i", $userId);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch all warehouses
$warehouses = $conn->query("SELECT * FROM warehouses")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="styles/dashstyle.css">
    <link rel="icon" type="image/png" href="img/inventory.png">
    <script src="https://use.fontawesome.com/0c7a3095b5.js"></script>

</head>
<body>
    <div id="dashboardMainContainer">
        <div class="dashboard_sidebar">
            <h3 class="dashboard_logo">Inventory.IO</h3>
            <div class="dashboard_sidebar_user">
                <img src="img/userimage.jpg" alt="User Image"><br>
                <span><?php echo htmlspecialchars($fullName); ?></span>
            </div>
            <div class="dashboard_sidebar_menus">
                <ul class="dashboard_menu_lists">
                    <li>
                        <a href="dashboard.php"><i class="fa fa-dashboard"></i> Dashboard</a>
                    </li>
                    <?php if ($roleId == 1): // Admin role ?>
                    <li>
                        <a href="admin_dashboard.php"><i class="fa fa-users"></i> Manage Users</a>
                    </li>
                    <li>
                        <a href="manage_warehouses.php"><i class="fa fa-truck"></i> Manage Warehouses</a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a href="manage_products.php"><i class="fa fa-product-hunt"></i> Manage Products</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="dashboard_content_container">
            <div class="dashboard_topNav">
                <a href="logout.php" id="logoutBtn"><i class="fa fa-power-off"></i> Log Out</a>
            </div>
            <div class="dashboard_content">
                <div class="dashboard_content_main">

                    <div class="form_styles">        
                        <h2>Manage Products</h2>

                        <!-- Add Product Form -->
                        <form method="post" action="manage_products.php">
                            <div class="textbox">
                                <label for="product_name">Product Name</label>
                                <input type="text" name="product_name" id="product_name" required>
                            </div>
                            <div class="textbox">
                                <label for="quantity">Quantity</label>
                                <input type="number" name="quantity" id="quantity" required>
                            </div>
                            <div class="textbox">
                                <label for="warehouse_id">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" required>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?php echo $warehouse['id']; ?>"><?php echo ucwords(htmlspecialchars($warehouse['warehouse_name'])); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_product" class="btn">Add Product</button>
                        </form>

                    </div>      
                    <h2>Existing Products</h2>
                    <!--download existing products-->
                    <?php if ($roleId == 1): // Admin role ?><button><a href="download_existing_products.php" class="btn btn-primary" style="margin-top: 10px;">Download Existing Products</a></button><?php endif; ?>

                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Warehouse</th>
                                <?php if ($roleId == 1): // Admin role ?>
                                <th>Created By</th>
                                <?php endif; ?>
                                <th>Last Update</th>
                                <th>Actions</th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo ucwords(htmlspecialchars($product["product_name"])); ?></td>
                                    <td><?php echo htmlspecialchars($product["quantity"]); ?></td>
                                    <td><?php echo ucwords(htmlspecialchars($product["warehouse_name"])); ?></td>
                                    <?php if ($roleId == 1): // Admin role ?>
                                    <td><?php echo ucwords(htmlspecialchars($product["user_full_name"])); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($product["updated_at"]); ?></td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>">Edit</a> |
                                        <a href="manage_products.php?delete_id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>