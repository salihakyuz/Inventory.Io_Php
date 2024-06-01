<?php
session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit();
}

require_once "connection.php";

if (isset($_POST["submit"])) {
    $fullName = $_POST["fullname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $passwordRepeat = $_POST["repeat_password"];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $errors = [];

    if (empty($fullName) || empty($email) || empty($password) || empty($passwordRepeat)) {
        $errors[] = "All fields are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email is not valid.";
    }
    if (strlen($password) < 8) {
        $errors[] = "Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character";
    }
    if ($password !== $passwordRepeat) {
        $errors[] = "Passwords do not match.";
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Email already exists!";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?, ?, ?, ?)");
        $role_id = 2; // Default role is user
        $stmt->bind_param("sssi", $fullName, $email, $passwordHash, $role_id);

        if ($stmt->execute()) {
            //echo '<script>alert("You are registered successfully.")</script>'; 

            echo "<div class='alert alert-success'>You are registered successfully.</div>";
        } else {
            die("Something went wrong.");
        }
    } else {
        foreach ($errors as $error) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory.IO-Registration</title>
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
    <h1>INVENTORY MANAGEMENT SYSTEM REGISTRATION</h1>
    <div class="login-box">
        <form action="registration.php" method="post">
            <div class="textbox">
                <input type="text" name="fullname" placeholder="Full Name:" required>
            </div>
            <div class="textbox">
                <input type="email" name="email" placeholder="Email:" required>
            </div>
            <div class="textbox">
                <input type="password" name="password" placeholder="Password:" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$" required>
                <small><i>*Minimum eight characters, at least one uppercase letter, one lowercase letter, one number and one special character</i></small>
            </div>
            <div class="textbox">
                <input type="password" name="repeat_password" placeholder="Repeat Password:" required>
            </div>
            <button type="submit" name="submit" class="btn">Submit</button>
        </form>
        <br>
        <div><p>Already registered? <a href="login.php">Login Here</a></p></div>
    </div>
</div>
</body>
</html>