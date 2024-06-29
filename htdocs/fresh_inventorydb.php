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

function loadXml($filePath) {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_file($filePath);
    if ($xml === false) {
        echo "Failed to load XML file: $filePath\n";
        foreach (libxml_get_errors() as $error) {
            echo "\t", $error->message;
        }
        libxml_clear_errors();
        return null;
    }
    return $xml;
}

$productsXml = loadXml('fresh_products.xml');
if ($productsXml === null) {
    exit;
}
$productDetails = [];

foreach ($productsXml->products->product as $product) {
    $name = (string) $product->name;
    $subcategory = (string) $product->category;
    $price = (float) $product->price;

    $productDetails[$name] = [
        'subcategory' => $subcategory,
        'price' => $price
    ];
}

$inventoryXml = loadXml('fresh_inventory.xml');
if ($inventoryXml === null) {
    exit;
}

foreach ($inventoryXml->products->product as $item) {
    $name = (string) $item['name'];
    $quantity = (int) $item['quantity'];

    if (isset($productDetails[$name])) {
        $sql = "INSERT INTO Inventory (Name, Category, Subcategory, UnitPrice, Quantity) 
                VALUES (:name, 'fresh_products', :subcategory, :price, :quantity) 
                ON DUPLICATE KEY UPDATE 
                Subcategory = :newSubcategory, 
                UnitPrice = :newPrice, 
                Quantity = :newQuantity";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':subcategory', $productDetails[$name]['subcategory'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $productDetails[$name]['price'], PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        // Bind the same values again for the ON DUPLICATE KEY UPDATE
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
