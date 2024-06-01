<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 1) {
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

// Handle form submission for adding a new warehouse
if (isset($_POST["add_warehouse"])) {
    $warehouseName = $_POST["warehouse_name"];
    $location = $_POST["location"];
    $stmt = $conn->prepare("INSERT INTO warehouses (warehouse_name, location) VALUES (?, ?)");
    $stmt->bind_param("ss", $warehouseName, $location);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_warehouses.php");
    exit();
}

// Handle form submission for editing a warehouse
if (isset($_POST["edit_warehouse"])) {
    $warehouseId = $_POST["warehouse_id"];
    $warehouseName = $_POST["warehouse_name"];
    $location = $_POST["location"];
    $stmt = $conn->prepare("UPDATE warehouses SET warehouse_name = ?, location = ? WHERE id = ?");
    $stmt->bind_param("ssi", $warehouseName, $location, $warehouseId);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_warehouses.php");
    exit();
}

// Handle deletion of a warehouse
if (isset($_GET["delete_id"])) {
    $warehouseId = $_GET["delete_id"];
    $stmt = $conn->prepare("DELETE FROM warehouses WHERE id = ?");
    $stmt->bind_param("i", $warehouseId);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_warehouses.php");
    exit();
}

// Fetch all warehouses
$warehouses = $conn->query("SELECT * FROM warehouses")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Manage Warehouses</title>
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
                    <li>
                        <a href="admin_dashboard.php"><i class="fa fa-users"></i> Manage Users</a>
                    </li>
                    <li>
                        <a href="manage_warehouses.php"><i class="fa fa-truck"></i> Manage Warehouses</a>
                    </li>
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
                        <h2>Manage Warehouses</h2>

                        <!-- Add Warehouse Form -->
                        <form method="post" action="manage_warehouses.php">
                            <div class="textbox">
                                <label for="warehouse_name">Warehouse Name</label>
                                <input type="text" name="warehouse_name" id="warehouse_name" required>
                            </div>
                            <div class="textbox">
                                <label for="location">Location</label>
                                <input type="text" name="location" id="location" required>
                            </div>
                            <button type="submit" name="add_warehouse" class="btn">Add Warehouse</button>
                        </form>
                    </div>
                    <h2>Existing Warehouses</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Warehouse Name</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <tr>
                                    <td><?php echo ucwords(htmlspecialchars($warehouse["warehouse_name"])); ?></td>
                                    <td><?php echo ucwords(htmlspecialchars($warehouse["location"])); ?></td>
                                    <td>
                                        <a href="edit_warehouse.php?id=<?php echo $warehouse['id']; ?>">Edit</a> | 
                                        <a href="manage_warehouses.php?delete_id=<?php echo $warehouse['id']; ?>" onclick="return confirm('Are you sure you want to delete this warehouse?');">Delete</a>
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