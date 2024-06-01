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
// Fetch user details
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Fetch all roles
    $roles = $conn->query("SELECT * FROM roles")->fetch_all(MYSQLI_ASSOC);
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $roleId = $_POST["role_id"];
        $stmt = $conn->prepare("UPDATE users SET role_id = ? WHERE id = ?");
        $stmt->bind_param("ii", $roleId, $userId);
        if ($stmt->execute()) {
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Failed to update user role.";
        }
    }
} else {
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Dashboard</title>
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
                        <h2>Edit User Role</h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="post" action="edit_user.php?id=<?php echo $user['id']; ?>">
                            <div class="textbox">
                                <label for="full_name">Full Name</label>
                                <input type="text" name="full_name" id="full_name" value="<?php echo ucwords(htmlspecialchars($user['full_name'])); ?>" disabled>
                            </div>
                            <div class="textbox">
                                <label for="email">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            <div class="textbox">
                                <label for="role_id">Role</label>
                                <select name="role_id" id="role_id">
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['id']; ?>" <?php if ($role['id'] == $user['role_id']) echo 'selected'; ?>>
                                            <?php echo ucwords(htmlspecialchars($role['role_name'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn">Update Role</button>
                        </form>
                    </div>    
                </div>
            </div>
        </div>
    </div>
</body>
</html>