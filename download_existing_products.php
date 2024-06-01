<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once "connection.php";

$userId = $_SESSION["user_id"];
$roleId = $_SESSION["role_id"];

if ($roleId == 1) { // Admin role
    $stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name, users.full_name as user_full_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id JOIN users ON products.user_id = users.id");
} else {
    $stmt = $conn->prepare("SELECT products.*, warehouses.warehouse_name FROM products JOIN warehouses ON products.warehouse_id = warehouses.id WHERE products.user_id = ?");
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$filename = "products_" . date('Ymd') . ".csv";
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');
//fix UTF-8 in Excel
fwrite($output, "\xEF\xBB\xBF");

if ($roleId == 1) { // Admin role
    fputcsv($output, array('Product Name', 'Quantity', 'Warehouse', 'Created By', 'Last Update'));
    foreach ($products as $product) {
        fputcsv($output, array(
            $product["product_name"],
            $product["quantity"],
            $product["warehouse_name"],
            $product["user_full_name"],
            $product["updated_at"]
        ));
    }
}

fclose($output);
exit();
?>
