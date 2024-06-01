<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";

$userId = $_SESSION["user_id"];
$roleId = $_SESSION["role_id"];

// Fetch products for the logged-in user
$stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id WHERE products.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Create a csv file
$filename = "products_" . $userId . ".csv";
header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=' . $filename);

$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF"); // to fix the letter in csv (utf-8)
fputcsv($output, array('Product Name', 'Quantity', 'Warehouse'));

foreach ($products as $product) {
    fputcsv($output, array($product["product_name"], $product["quantity"], $product["warehouse_name"]));
}

fclose($output);
exit();
?>
