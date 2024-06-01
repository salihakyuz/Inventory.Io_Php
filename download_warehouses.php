<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";

$roleId = $_SESSION["role_id"];

// Ensure only users with permission (e.g., admin) can download the warehouses table
if ($roleId != 1) {
    header("Location: dashboard.php");
    exit();
}

// Fetch warehouses
$stmt = $conn->prepare("SELECT * FROM warehouses");
$stmt->execute();
$warehouses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Create a CSV file
$filename = "warehouses.csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=' . $filename);

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // to fix the letter in csv (utf-8)
fputcsv($output, array('Warehouse Name', 'Location'));

foreach ($warehouses as $warehouse) {
    fputcsv($output, array($warehouse["warehouse_name"], $warehouse["location"]));
}

fclose($output);
exit();
?>
