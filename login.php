<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    require_once "connection.php";

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["role_id"] = $user["role_id"];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory.IO-Login</title>
    <link rel="stylesheet" href="styles/logstyle.css">
    <link rel="icon" type="image/png" href="img/inventory.png">
</head>
<body>
    <div class="navbar">
        <div class="logo">
            <h3>Inventory.io</h3>
        </div>
        <div class="menu">
            <a href="homepage.html">Home</a>
        </div>
    </div>

    <div class="login-container">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h1>INVENTORY MANAGEMENT SYSTEM</h1>
        <div class="login-box">
            <form action="login.php" method="post">
                <div class="textbox">
                    <label for="email">Email</label>
                    <input type="email" name="email" placeholder="Enter Email:" required>
                </div>
                <div class="textbox">
                    <label for="password">Password</label>
                    <input type="password" name="password" placeholder="Enter Password:" required>
                </div>
                <button type="submit" name="login" class="btn">Login</button>
            </form>
            <br>
            <div><p>Not registered yet? <a href="registration.php">Register Here</a></p></div>
        </div>
    </div>
</body>
</html>