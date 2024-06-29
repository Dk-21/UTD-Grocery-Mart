<?php
$host = 'localhost';
$dbname = 'UTDMart';
$username = 'admin';
$password = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function loadJson($filePath) {
    $jsonString = file_get_contents($filePath);
    if ($jsonString === false) {
        echo "Failed to load JSON file: $filePath\n";
        return null;
    }
    return json_decode($jsonString, true);
}

$productsJson = loadJson('baking_products.json');
if ($productsJson === null) {
    exit;
}
$productDetails = [];

foreach ($productsJson['products'] as $product) {
    $name = $product['name'];
    $subcategory = $product['category'];
    $price = $product['price'];

    $productDetails[$name] = [
        'subcategory' => $subcategory,
        'price' => $price
    ];
}

$inventoryJson = loadJson('baking_Inventory.json');
if ($inventoryJson === null) {
    exit;
}

foreach ($inventoryJson['inventory'] as $name => $quantity) {
    if (isset($productDetails[$name])) {
        $sql = "INSERT INTO Inventory (Name, Category, Subcategory, UnitPrice, Quantity) 
                VALUES (:name, 'Baking', :subcategory, :price, :quantity) 
                ON DUPLICATE KEY UPDATE 
                Subcategory = :newSubcategory, 
                UnitPrice = :newPrice, 
                Quantity = :newQuantity";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':subcategory', $productDetails[$name]['subcategory'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $productDetails[$name]['price'], PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':newSubcategory', $productDetails[$name]['subcategory'], PDO::PARAM_STR);
        $stmt->bindParam(':newPrice', $productDetails[$name]['price'], PDO::PARAM_STR);
        $stmt->bindParam(':newQuantity', $quantity, PDO::PARAM_INT);

        if (!$stmt->execute()) {
            print_r($stmt->errorInfo());  // Print any error information
        }
    }
}

echo "Database updated successfully.\n";
?>
