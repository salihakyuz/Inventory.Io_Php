<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";
//to show user name in side-bar
$userId = $_SESSION["user_id"];
$roleId = $_SESSION["role_id"];

// Fetch user information
$stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($fullName);
$stmt->fetch();
$stmt->close();

// Fetch products for the logged-in user
$stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id WHERE products.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch warehouses if the user has permission
$warehouses = [];
if ($roleId == 1) { // Admin role
    $stmt = $conn->prepare("SELECT * FROM warehouses");
    $stmt->execute();
    $warehouses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory.io - Dashboard</title>
    <link rel="icon" type="image/png" href="img/inventory.png">
    <script src="https://use.fontawesome.com/0c7a3095b5.js"></script>
    <link rel="stylesheet" href="styles/dashstyle.css">
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
                <!--button for download products-->

                    <h2>Your Products</h2>
                    <button><a href="download_csv.php" class="btn btn-primary" style="margin-top: 10px;">Download Products</a></button>

                    <table>
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Warehouse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo ucwords(htmlspecialchars($product["product_name"])); ?></td>
                                    <td><?php echo ucwords(htmlspecialchars($product["quantity"])); ?></td>
                                    <td><?php echo ucwords(htmlspecialchars($product["warehouse_name"])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <br>            
                    <?php if ($roleId == 1): ?>
                    <!--button for download warehouses-->

                    <h2>Warehouses</h2>
                    <button><a href="download_warehouses.php" class="btn btn-primary" style="margin-top: 10px;">Download Warehouses</a></button>

                    <table>
                        <thead>
                            <tr>
                                <th>Warehouse Name</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <tr>
                                    <td><?php echo ucwords(htmlspecialchars($warehouse["warehouse_name"])); ?></td>
                                    <td><?php echo ucwords(htmlspecialchars($warehouse["location"])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>