<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";

// To show user name in side-bar
$userId = $_SESSION["user_id"];
$roleId = $_SESSION["role_id"];

// Fetch user information
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Fetch product details
if (isset($_GET['id'])) {
    $productId = $_GET['id'];

    if ($roleId == 1) {
        // Admin can access any product
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
    } else {
        // Regular user can only access their own products
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $productId, $userId);
    }

    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // If the product does not exist or does not belong to the user, redirect
    if (!$product) {
        header("Location: manage_products.php");
        exit();
    }

    // Fetch all warehouses
    $warehouses = $conn->query("SELECT * FROM warehouses")->fetch_all(MYSQLI_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $productName = $_POST["product_name"];
        $quantity = $_POST["quantity"];
        $warehouseId = $_POST["warehouse_id"];


        
        $stmt = $conn->prepare("UPDATE products SET warehouse_id = ?, product_name = ?,updated_at=NOW(), quantity = ? WHERE id = ?");
        $stmt->bind_param("isii", $warehouseId, $productName, $quantity, $productId);
        
        if ($stmt->execute()) {
            header("Location: manage_products.php");
            exit();
        } else {
            $error = "Failed to update product: " . $stmt->error;
        }
    }
} else {
    header("Location: manage_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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

                        <h2>Edit Product</h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" action="edit_product.php?id=<?php echo htmlspecialchars($product['id']); ?>">
                            <div class="textbox">
                                <label for="product_name">Product Name</label>
                                <input type="text" name="product_name" id="product_name" value="<?php echo ucwords(htmlspecialchars($product['product_name'])); ?>" required>
                            </div>
                            <div class="textbox">
                                <label for="quantity">Quantity</label>
                                <input type="number" name="quantity" id="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                            </div>
                            <div class="textbox">
                                <label for="warehouse_id">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" required>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?php echo $warehouse['id']; ?>" <?php if ($warehouse['id'] == $product['warehouse_id']) echo 'selected'; ?>>
                                            <?php echo ucwords(htmlspecialchars($warehouse['warehouse_name'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn">Update Product</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
