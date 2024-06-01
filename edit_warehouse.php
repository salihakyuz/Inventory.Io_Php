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

// Fetch warehouse details
if (isset($_GET['id'])) {
    $warehouseId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM warehouses WHERE id = ?");
    $stmt->bind_param("i", $warehouseId);
    $stmt->execute();
    $warehouse = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $warehouseName = $_POST["warehouse_name"];
        $location = $_POST["location"];
        $stmt = $conn->prepare("UPDATE warehouses SET warehouse_name = ?, location = ? WHERE id = ?");
        $stmt->bind_param("ssi", $warehouseName, $location, $warehouseId);
        if ($stmt->execute()) {
            header("Location: manage_warehouses.php");
            exit();
        } else {
            $error = "Failed to update warehouse.";
        }
    }
} else {
    header("Location: manage_warehouses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Warehouse - Admin Dashboard</title>
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
                        <h2>Edit Warehouse</h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" action="edit_warehouse.php?id=<?php echo $warehouse['id']; ?>">
                            <div class="textbox">
                                <label for="warehouse_name">Warehouse Name</label>
                                <input type="text" name="warehouse_name" id="warehouse_name" value="<?php echo htmlspecialchars($warehouse['warehouse_name']); ?>" required>
                            </div>
                            <div class="textbox">
                                <label for="location">Location</label>
                                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($warehouse['location']); ?>" required>
                            </div>
                            <button type="submit" class="btn">Update Warehouse</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>